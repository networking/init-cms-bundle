<?php

namespace Networking\InitCmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('networking_init_cms');

        $rootNode
            ->children()
                ->scalarNode('init_cms_editor')->defaultValue('ckeditor')->end()
                ->scalarNode('ckeditor_config')->defaultValue('')->end()
                ->scalarNode('translation_fallback_route')->defaultValue('initcms_404')->end()
                ->scalarNode('404_template')->isRequired()->end()
                ->scalarNode('no_translation_template')->isRequired()->end()
                ->arrayNode('languages')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('label')->isRequired()->end()
                            ->scalarNode('short_label')->end()
                            ->scalarNode('locale')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('content_types')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('class')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('templates')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('key')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('template')->isRequired()->end()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('icon')->end()
                            ->arrayNode('zones')
                            ->requiresAtLeastOneElement()
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')->isRequired()->end()
                                        ->scalarNode('span')->isRequired()->end()
                                        ->scalarNode('max_content_items')->defaultValue(0)->end()
                                        ->arrayNode('restricted_types')->prototype('scalar')->end()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
