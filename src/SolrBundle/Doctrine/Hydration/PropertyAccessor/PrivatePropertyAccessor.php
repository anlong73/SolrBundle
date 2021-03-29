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

class PrivatePropertyAccessor implements PropertyAccessorInterface
{
    /**
     * @var \ReflectionProperty
     */
    private $classProperty;

    /**
     * @param \ReflectionProperty $classProperty
     */
    public function __construct(\ReflectionProperty $classProperty)
    {
        $this->classProperty = $classProperty;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($targetObject, $value)
    {
        $this->classProperty->setAccessible(true);
        $this->classProperty->setValue($targetObject, $value);
    }
}
