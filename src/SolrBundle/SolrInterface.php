<?php

/*
 * Solr Bundle
 * This is a fork of the unmaintained solr bundle from Florian Semm.
 *
 * @author Daan Biesterbos     (fork maintainer)
 * @author Florian Semm (author original bundle)
 *
 * Issues can be submitted here:
 * https://github.com/daanbiesterbos/SolrBundle/issues
 */

namespace FS\SolrBundle;

use FS\SolrBundle\Query\AbstractQuery;
use FS\SolrBundle\Query\QueryBuilderInterface;
use FS\SolrBundle\Repository\Repository;
use FS\SolrBundle\Repository\RepositoryInterface;

interface SolrInterface
{
    /**
     * @param object|string $entity entity, entity-alias or classname
     */
    public function removeDocument($entity);

    /**
     * @param object|string $entity entity, entity-alias or classname
     *
     * @return bool
     */
    public function addDocument($entity): bool;

    /**
     * @param AbstractQuery $query
     *
     * @throws SolrException
     *
     * @return array of found documents
     */
    public function query(AbstractQuery $query): array;

    /**
     * @param object|string $entity entity, entity-alias or classname
     *
     * @return bool
     */
    public function updateDocument($entity): bool;

    /**
     * @param object|string $entity entity, entity-alias or classname
     *
     * @throws SolrException if repository of the given $entityAlias does not extend FS\SolrBundle\Repository\Repository
     *
     * @return RepositoryInterface
     */
    public function getRepository($entity): RepositoryInterface;

    /**
     * @param object|string $entity entity, entity-alias or classname
     *
     * @return QueryBuilderInterface
     */
    public function createQueryBuilder($entity): QueryBuilderInterface;
}
