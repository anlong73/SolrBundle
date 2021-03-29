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

namespace FS\SolrBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds plugins tagged with solarium.client.plugin directly to Solarium.
 */
class AddSolariumPluginsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $plugins = $container->findTaggedServiceIds('solarium.client.plugin');

        $clientBuilder = $container->getDefinition('solr.client.adapter.builder');
        foreach ($plugins as $service => $definition) {
            $clientBuilder->addMethodCall(
                'addPlugin',
                [
                    $definition[0]['plugin-name'],
                    new Reference($service),
                ]
            );
        }
    }
}
