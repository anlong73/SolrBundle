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

namespace FS\SolrBundle\Tests\DependencyInjection;

use FS\SolrBundle\DependencyInjection\FSSolrExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @group extension
 */
class FSSolrExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container = null;

    public function testDoctrineORMSetup()
    {
        $this->enableOrmConfig();
        $config = $this->commonConfig();

        $extension = new FSSolrExtension();
        $extension->load($config, $this->container);

        $this->assertTrue($this->container->has('solr.document.orm.subscriber'), 'orm subscriber');

        $this->assertDefinitionHasTag('solr.document.orm.subscriber', 'doctrine.event_subscriber');

        $this->assertClassnameResolverHasOrmDefaultConfiguration();
    }

    public function testDoctrineODMSetup()
    {
        $config = $this->commonConfig();
        $this->enableOdmConfig();

        $extension = new FSSolrExtension();
        $extension->load($config, $this->container);

        $this->assertTrue($this->container->has('solr.document.odm.subscriber'), 'odm subscriber');

        $this->assertDefinitionHasTag('solr.document.odm.subscriber', 'doctrine_mongodb.odm.event_subscriber');

        $this->assertClassnameResolverHasOdmDefaultConfiguration();
    }

    /**
     * @test
     */
    public function solrListensToOdmAndOrmEvents()
    {
        $config = $this->commonConfig();
        $this->enableOdmConfig();
        $this->enableOrmConfig();

        $extension = new FSSolrExtension();
        $extension->load($config, $this->container);

        $this->assertTrue($this->container->has('solr.document.odm.subscriber'), 'odm subscriber');
        $this->assertDefinitionHasTag('solr.document.odm.subscriber', 'doctrine_mongodb.odm.event_subscriber');

        $this->assertTrue($this->container->has('solr.document.orm.subscriber'), 'orm subscriber');
        $this->assertDefinitionHasTag('solr.document.orm.subscriber', 'doctrine.event_subscriber');
    }

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
    }

    private function enableOdmConfig()
    {
        $this->container->setParameter('doctrine_mongodb.odm.document_managers', ['default' => 'odm.default.mananger']);
    }

    private function enableOrmConfig()
    {
        $this->container->setParameter('doctrine.entity_managers', ['default' => 'orm.default.mananger']);
    }

    private function commonConfig()
    {
        return [[
            'endpoints' => [
                'default' => [
                    'host' => '192.168.178.24',
                    'port' => 8983,
                    'path' => '/solr/',
                ],
            ],
        ]];
    }

    private function assertClassnameResolverHasOrmDefaultConfiguration()
    {
        $doctrineConfiguration = $this->getReferenzIdOfCalledMethod();

        $this->assertEquals('doctrine.orm.default_configuration', $doctrineConfiguration);
    }

    private function assertClassnameResolverHasOdmDefaultConfiguration()
    {
        $doctrineConfiguration = $this->getReferenzIdOfCalledMethod();

        $this->assertEquals('doctrine_mongodb.odm.default_configuration', $doctrineConfiguration);
    }

    /**
     * @return Reference
     */
    private function getReferenzIdOfCalledMethod()
    {
        $methodCalls = $this->container->getDefinition('solr.doctrine.classnameresolver.known_entity_namespaces')->getMethodCalls();

        $firstMethodCall = $methodCalls[0];
        $references = $firstMethodCall[1];
        $reference = $references[0];

        return $reference;
    }

    private function assertDefinitionHasTag($definition, $tag)
    {
        $tags = $this->container->getDefinition($definition)->getTags();

        $this->assertTrue(
            $this->container->getDefinition($definition)->hasTag($tag),
            sprintf('%s with %s tag, has %s', $definition, $tag, print_r($tags, true))
        );
    }
}
