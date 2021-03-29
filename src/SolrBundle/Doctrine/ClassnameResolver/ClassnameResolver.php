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

/**
 * Determines the full qualified classname of an entity-alias.
 */
class ClassnameResolver
{
    /**
     * @var KnownNamespaceAliases
     */
    private $knownNamespaceAliases;

    /**
     * @param KnownNamespaceAliases $knownNamespaceAliases
     */
    public function __construct(KnownNamespaceAliases $knownNamespaceAliases)
    {
        $this->knownNamespaceAliases = $knownNamespaceAliases;
    }

    /**
     * @param string $entityAlias
     *
     * @throws ClassnameResolverException if the entityAlias could not find in any configured namespace or the class
     *                                    does not exist
     *
     * @return string
     */
    public function resolveFullQualifiedClassname($entityAlias)
    {
        $entityNamespaceAlias = $this->getNamespaceAlias($entityAlias);

        if (false === $this->knownNamespaceAliases->isKnownNamespaceAlias($entityNamespaceAlias)) {
            $e = ClassnameResolverException::fromKnownNamespaces(
                $entityNamespaceAlias,
                $this->knownNamespaceAliases->getAllNamespaceAliases()
            );

            throw $e;
        }

        $foundNamespace = $this->knownNamespaceAliases->getFullyQualifiedNamespace($entityNamespaceAlias);

        $realClassName = $this->getFullyQualifiedClassname($foundNamespace, $entityAlias);
        if (false === class_exists($realClassName)) {
            throw new ClassnameResolverException(sprintf('class %s does not exist', $realClassName));
        }

        return $realClassName;
    }

    /**
     * @param string $entity
     *
     * @return string
     */
    public function getNamespaceAlias($entity)
    {
        list($namespaceAlias, $simpleClassName) = explode(':', $entity);

        return $namespaceAlias;
    }

    /**
     * @param string $entity
     *
     * @return string
     */
    public function getClassname($entity)
    {
        list($namespaceAlias, $simpleClassName) = explode(':', $entity);

        return $simpleClassName;
    }

    /**
     * @param string $namespace
     * @param string $entityAlias
     *
     * @return string
     */
    private function getFullyQualifiedClassname($namespace, $entityAlias)
    {
        $realClassName = $namespace.'\\'.$this->getClassname($entityAlias);

        return $realClassName;
    }
}
