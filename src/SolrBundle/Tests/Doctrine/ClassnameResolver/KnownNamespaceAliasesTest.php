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

namespace FS\SolrBundle\Tests\Doctrine\ClassnameResolver;

use Doctrine\ODM\MongoDB\Configuration as OdmConfiguration;
use Doctrine\ORM\Configuration as OrmConfiguration;
use FS\SolrBundle\Doctrine\ClassnameResolver\KnownNamespaceAliases;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class KnownNamespaceAliasesTest extends TestCase
{
    private $logger;

    /**
     * @test
     */
    public function addAliasFromMultipleOrmConfigurations()
    {
        $config1 = $this->createMock(OrmConfiguration::class);
        $config1->expects($this->once())
            ->method('getEntityNamespaces')
            ->willReturn(['AcmeDemoBundle']);

        $config2 = $this->createMock(OrmConfiguration::class);
        $config2->expects($this->once())
            ->method('getEntityNamespaces')
            ->willReturn(['AcmeBlogBundle']);

        $knownAliases = new KnownNamespaceAliases($this->logger, sys_get_temp_dir());
        $knownAliases->addEntityNamespaces($config1);
        $knownAliases->addEntityNamespaces($config2);

        $namespaceAliases = $knownAliases->getAllNamespaceAliases();
        $this->assertTrue(in_array('AcmeDemoBundle', $namespaceAliases, true));
        $this->assertTrue(in_array('AcmeBlogBundle', $namespaceAliases, true));
    }

    /**
     * @test
     */
    public function addAliasFromMultipleOdmConfigurations()
    {
        $config1 = $this->createMock(OdmConfiguration::class);
        $config1->expects($this->once())
            ->method('getDocumentNamespaces')
            ->willReturn(['AcmeDemoBundle']);

        $config2 = $this->createMock(OdmConfiguration::class);
        $config2->expects($this->once())
            ->method('getDocumentNamespaces')
            ->willReturn(['AcmeBlogBundle']);

        $knownAliases = new KnownNamespaceAliases($this->logger, sys_get_temp_dir());
        $knownAliases->addDocumentNamespaces($config1);
        $knownAliases->addDocumentNamespaces($config2);

        $this->assertTrue(in_array('AcmeDemoBundle', $knownAliases->getAllNamespaceAliases(), true));
        $this->assertTrue(in_array('AcmeBlogBundle', $knownAliases->getAllNamespaceAliases(), true));
    }

    /**
     * @test
     */
    public function knowAliasHasAValidNamespace()
    {
        $config1 = $this->createMock(OdmConfiguration::class);
        $config1->expects($this->once())
            ->method('getDocumentNamespaces')
            ->willReturn(['AcmeDemoBundle' => 'Acme\DemoBundle\Document']);

        $config2 = $this->createMock(OdmConfiguration::class);
        $config2->expects($this->once())
            ->method('getDocumentNamespaces')
            ->willReturn(['AcmeBlogBundle' => 'Acme\BlogBundle\Document']);

        $knownAliases = new KnownNamespaceAliases($this->logger, sys_get_temp_dir());
        $knownAliases->addDocumentNamespaces($config1);
        $knownAliases->addDocumentNamespaces($config2);

        $this->assertEquals('Acme\DemoBundle\Document', $knownAliases->getFullyQualifiedNamespace('AcmeDemoBundle'));
    }

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }
}
