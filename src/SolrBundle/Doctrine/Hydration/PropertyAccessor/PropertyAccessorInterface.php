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

namespace FS\SolrBundle\Doctrine\Hydration\PropertyAccessor;

interface PropertyAccessorInterface
{
    /**
     * @param object $targetObject
     * @param mixed  $value
     */
    public function setValue($targetObject, $value);
}
