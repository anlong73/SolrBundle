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

namespace FS\SolrBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('fs_solr');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode->children()
            ->arrayNode('endpoints')
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->children()
                        ->scalarNode('dsn')->end()
                        ->scalarNode('scheme')->end()
                        ->scalarNode('host')->end()
                        ->scalarNode('port')->end()
                        ->scalarNode('path')->end()
                        ->scalarNode('core')->end()
                        ->scalarNode('timeout')->end()
                        ->booleanNode('active')->defaultValue(true)->end()
                    ->end()
                ->end()
            ->end()
            ->booleanNode('auto_index')->defaultValue(true)->end()
        ->end();

        return $treeBuilder;
    }
}
