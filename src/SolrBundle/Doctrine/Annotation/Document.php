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

namespace FS\SolrBundle\Doctrine\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Defines a solr-document.
 *
 * @Annotation
 */
class Document extends Annotation
{
    /**
     * @var string
     */
    public $repository = '';

    /**
     * @var int
     */
    public $boost = 0;

    /**
     * @var string
     */
    public $index = null;

    /**
     * @var string
     */
    public $indexHandler;

    /**
     * @return number
     */
    public function getBoost()
    {
        return $this->boost;
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }
}
