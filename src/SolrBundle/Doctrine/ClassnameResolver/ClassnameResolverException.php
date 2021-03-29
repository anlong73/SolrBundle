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

namespace FS\SolrBundle\Doctrine\ClassnameResolver;

class ClassnameResolverException extends \RuntimeException
{
    /**
     * @param string $entityNamespaceAlias
     * @param array  $knownNamespaces
     *
     * @return ClassnameResolverException
     */
    public static function fromKnownNamespaces($entityNamespaceAlias, array $knownNamespaces)
    {
        $flattenListOfAllAliases = implode(',', $knownNamespaces);

        return new ClassnameResolverException(
            sprintf('could not resolve classname for entity %s, known aliase(s) are: %s', $entityNamespaceAlias, $flattenListOfAllAliases)
        );
    }
}
