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

namespace FS\SolrBundle\Tests\Fixtures;

use FS\SolrBundle\Doctrine\Annotation as Solr;

/**
 * @Solr\Document(repository="FS\SolrBundle\Tests\Fixtures\InvalidEntityRepository")
 */
class EntityWithInvalidRepository
{
    /**
     * @var int
     *
     * @Solr\Id
     */
    private $id;
}
