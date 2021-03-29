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

namespace FS\SolrBundle\Doctrine\Hydration;

/**
 * Used when the index is not based on/in sync with a Database.
 */
class NoDatabaseValueHydrator extends ValueHydrator
{
    /**
     * Let the original values from the index untouched.
     *
     * {@inheritdoc}
     */
    public function removePrefixedKeyValues($property)
    {
        return $property;
    }
}
