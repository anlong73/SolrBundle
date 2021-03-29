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

namespace FS\SolrBundle\Repository;

/**
 * Defines common finder-method for document-repositories.
 */
interface RepositoryInterface
{
    /**
     * @param array $args
     *
     * @return array
     */
    public function findBy(array $args);

    /**
     * @param int $id
     *
     * @return object
     */
    public function find($id);

    /**
     * @param array $args
     *
     * @return object
     */
    public function findOneBy(array $args);

    /**
     * @return array
     */
    public function findAll();
}
