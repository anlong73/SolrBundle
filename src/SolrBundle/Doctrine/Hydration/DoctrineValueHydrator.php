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

use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;

class DoctrineValueHydrator extends ValueHydrator
{
    /**
     * {@inheritdoc}
     */
    public function mapValue($fieldName, $value, MetaInformationInterface $metaInformation)
    {
        if (is_array($value)) {
            return false;
        }

        // is object with getter
        if ($metaInformation->getField($fieldName) && $metaInformation->getField($fieldName)->getter) {
            return false;
        }

        return true;
    }
}
