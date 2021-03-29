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

namespace FS\SolrBundle\Tests\Solr\Doctrine;

use FS\SolrBundle\Doctrine\ClassnameResolver\ClassnameResolver;
use FS\SolrBundle\Doctrine\ClassnameResolver\ClassnameResolverException;
use FS\SolrBundle\Doctrine\ClassnameResolver\KnownNamespaceAliases;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntity;
use PHPUnit\Framework\TestCase;

/**
 * @group resolver
 */
class ClassnameResolverTest extends TestCase
{
    const ENTITY_NAMESPACE = 'FS\SolrBundle\Tests\Fixtures';
    const UNKNOW_ENTITY_NAMESPACE = 'FS\Unknown';

    private $knownAliases;

    /**
     * @test
     */
    public function resolveClassnameOfCommonEntity()
    {
        $resolver = $this->getResolverWithKnowNamespace(self::ENTITY_NAMESPACE);

        $this->assertEquals(ValidTestEntity::class, $resolver->resolveFullQualifiedClassname('FSTest:ValidTestEntity'));
    }

    /**
     * @test
     */
    public function cantResolveClassnameFromUnknowClassWithValidNamespace()
    {
        $this->expectException(ClassnameResolverException::class);
        $resolver = $this->getResolverWithOrmAndOdmConfigBothHasEntity(self::ENTITY_NAMESPACE);

        $resolver->resolveFullQualifiedClassname('FSTest:UnknownEntity');
    }

    /**
     * @test
     */
    public function cantResolveClassnameIfEntityNamespaceIsUnknown()
    {
        $this->expectException(ClassnameResolverException::class);
        $resolver = $this->getResolverWithOrmConfigPassedInvalidNamespace(self::UNKNOW_ENTITY_NAMESPACE);

        $resolver->resolveFullQualifiedClassname('FStest:entity');
    }

    protected function setUp(): void
    {
        $this->knownAliases = $this->createMock(KnownNamespaceAliases::class);
    }

    /**
     * both has a namespace.
     *
     * @param string $knownNamespace
     *
     * @return ClassnameResolver
     */
    private function getResolverWithOrmAndOdmConfigBothHasEntity($knownNamespace)
    {
        $this->knownAliases->expects($this->once())
            ->method('isKnownNamespaceAlias')
            ->willReturn(true);

        $this->knownAliases->expects($this->once())
            ->method('getFullyQualifiedNamespace')
            ->willReturn($knownNamespace);

        $resolver = new ClassnameResolver($this->knownAliases);

        return $resolver;
    }

    private function getResolverWithOrmConfigPassedInvalidNamespace($knownNamespace)
    {
        $this->knownAliases->expects($this->once())
            ->method('isKnownNamespaceAlias')
            ->willReturn(false);

        $this->knownAliases->expects($this->once())
            ->method('getAllNamespaceAliases')
            ->willReturn(['FSTest']);

        $resolver = new ClassnameResolver($this->knownAliases);

        return $resolver;
    }

    private function getResolverWithKnowNamespace($knownNamespace)
    {
        $this->knownAliases->expects($this->once())
            ->method('isKnownNamespaceAlias')
            ->willReturn(true);

        $this->knownAliases->expects($this->once())
            ->method('getFullyQualifiedNamespace')
            ->willReturn($knownNamespace);

        $resolver = new ClassnameResolver($this->knownAliases);

        return $resolver;
    }
}
