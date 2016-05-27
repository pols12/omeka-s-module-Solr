<?php

/*
 * Copyright BibLibre, 2016
 *
 * This software is governed by the CeCILL license under French law and abiding
 * by the rules of distribution of free software.  You can use, modify and/ or
 * redistribute the software under the terms of the CeCILL license as circulated
 * by CEA, CNRS and INRIA at the following URL "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and rights to copy, modify
 * and redistribute granted by the license, users are provided only with a
 * limited warranty and the software's author, the holder of the economic
 * rights, and the successive licensors have only limited liability.
 *
 * In this respect, the user's attention is drawn to the risks associated with
 * loading, using, modifying and/or developing or reproducing the software by
 * the user in light of its specific status of free software, that may mean that
 * it is complicated to manipulate, and that also therefore means that it is
 * reserved for developers and experienced professionals having in-depth
 * computer knowledge. Users are therefore encouraged to load and test the
 * software's suitability as regards their requirements in conditions enabling
 * the security of their systems and/or data to be ensured and, more generally,
 * to use and operate it in the same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL license and that you accept its terms.
 */

namespace Solr\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class SolrProfileAdapter extends AbstractEntityAdapter
{
    /**
     * {@inheritDoc}
     */
    protected $sortFields = [
        'id'            => 'id',
        'resource_name' => 'resourceName',
    ];

    /**
     * {@inheritDoc}
     */
    public function getResourceName()
    {
        return 'solr_profiles';
    }

    /**
     * {@inheritDoc}
     */
    public function getRepresentationClass()
    {
        return 'Solr\Api\Representation\SolrProfileRepresentation';
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityClass()
    {
        return 'Solr\Entity\SolrProfile';
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate(Request $request, EntityInterface $entity,
        ErrorStore $errorStore
    ) {
        if ($this->shouldHydrate($request, 'o:resource_name')) {
            $entity->setResourceName($request->getValue('o:resource_name'));
        }

        $this->hydrateSolrNode($request, $entity);
    }

    /**
     * {@inheritDoc}
     */
    public function validateEntity(EntityInterface $entity, ErrorStore $errorStore)
    {
        if (false == $entity->getResourceName()) {
            $errorStore->addError('o:resource_name', 'The name cannot be empty.');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function buildQuery(QueryBuilder $qb, array $query)
    {
        if (isset($query['solr_node_id'])) {
            $nodeAlias = $this->createAlias();
            $qb->innerJoin('Solr\Entity\SolrProfile.solrNode', $nodeAlias);
            $qb->andWhere($qb->expr()->eq(
                "$nodeAlias.id",
                $this->createNamedParameter($qb, $query['solr_node_id']))
            );
        }
        if (isset($query['resource_name'])) {
            $qb->andWhere($qb->expr()->eq(
                'Solr\Entity\SolrProfile.resourceName',
                $this->createNamedParameter($qb, $query['resource_name'])
            ));
        }
    }

    protected function hydrateSolrNode($request, $entity)
    {
        if ($this->shouldHydrate($request, 'o:solr_node')) {
            $data = $request->getContent();
            if (isset($data['o:solr_node']['o:id'])
                && is_numeric($data['o:solr_node']['o:id'])
            ) {
                $node = $this->getAdapter('solr_nodes')
                    ->findEntity($data['o:solr_node']['o:id']);
            } else {
                $node = null;
            }
            $entity->setSolrNode($node);
        }
    }
}
