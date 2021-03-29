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

class MethodCallPropertyAccessor implements PropertyAccessorInterface
{
    /**
     * @var string
     */
    private $setterName;

    /**
     * @param string $setterName
     */
    public function __construct($setterName)
    {
        $this->setterName = $setterName;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($targetObject, $value)
    {
        $targetObject->{$this->setterName}($value);
    }
}
