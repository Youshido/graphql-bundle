<?php

namespace Youshido\GraphQLBundle\src\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link
 * http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('graphql');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->scalarNode('schema_class')->defaultValue(null)->end()
            ->scalarNode('schema_service')->defaultValue(null)->end()
            ->integerNode('max_complexity')->defaultValue(null)->end()
            ->scalarNode('logger')->defaultValue(null)->end()
            ->arrayNode('security')
            ->addDefaultsIfNotSet()
            ->canBeUnset()
            ->children()
            ->arrayNode('guard')
            ->addDefaultsIfNotSet()
            ->canBeUnset()
            ->children()
            ->booleanNode('operation')->defaultFalse()->end()
            ->booleanNode('field')->defaultFalse()->end()
            ->end()
            ->end()
            ->arrayNode('white_list')
            ->canBeUnset()
            ->defaultValue([])
            ->prototype('scalar')->end()
            ->end()
            ->arrayNode('black_list')
            ->canBeUnset()
            ->defaultValue([])
            ->prototype('scalar')->end()
            ->end()
            ->end()
            ->end()
            ->arrayNode('response')
            ->addDefaultsIfNotSet()
            ->canBeUnset()
            ->children()
            ->booleanNode('json_pretty')->defaultTrue()->end()
            ->arrayNode('headers')
            ->prototype('array')
            ->children()
            ->scalarNode('name')->end()
            ->scalarNode('value')->defaultValue(null)->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}