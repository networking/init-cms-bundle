<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('networking_init_cms');

        // Keep compatibility with symfony/config < 4.2
        if (!method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->root('networking_init_cms');
        } else {
            $rootNode = $treeBuilder->getRootNode();
        }
        //mongodb is not yet fully supported but will come (eventually)
        $supportedDrivers = ['orm', 'mongodb'];

        $rootNode
            ->children()
                ->arrayNode('admin_toolbar')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('toolbar')->defaultTrue()->end()
                        ->scalarNode('position')
                            ->defaultValue('top')
                            ->validate()
                                ->ifNotInArray(['bottom', 'top'])
                                ->thenInvalid('The CSS position %s is not supported')
                        ->end()
                    ->end()
                ->end()
            ->end();
        $rootNode
            ->children()
                ->scalarNode('db_driver')
                    ->defaultValue('orm')
                    ->validate()
                    ->ifNotInArray($supportedDrivers)
                    ->thenInvalid('The driver %s is not supported. Please choose one of '.json_encode($supportedDrivers))
                    ->end()
                ->end()
                ->arrayNode('class')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('page')->defaultValue('App\\Entity\\Page')->end()
                        ->scalarNode('layout_block')->defaultValue('Networking\\InitCmsBundle\\Entity\\LayoutBlock')->end()
                        ->scalarNode('user')->defaultValue('App\\Entity\\User')->end()
                        ->scalarNode('menu_item')->defaultValue('Networking\\InitCmsBundle\\Entity\\MenuItem')->end()
                    ->end()
                ->end()
                ->scalarNode('allow_locale_cookie')->defaultTrue()->end()
                ->scalarNode('single_language')->defaultFalse()->end()
                ->scalarNode('translation_fallback_route')->defaultValue('initcms_404')->end()
                ->scalarNode('404_template')->defaultValue('@NetworkingInitCms/error_404.html.twig')->end()
                ->scalarNode('no_translation_template')->defaultValue('@NetworkingInitCms/no_translation_found.html.twig')->end()
                ->scalarNode('multiple_media_tags')->defaultValue(true)->end()
                ->scalarNode('show_tag_tree')->defaultValue(true)->end()
                ->arrayNode('languages')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('label')->isRequired()->end()
                            ->scalarNode('short_label')->end()
                            ->scalarNode('locale')->isRequired()->end()
                        ->end()
                    ->end()
                    ->defaultValue([['label' => 'English', 'short_label' => 'en', 'locale' => 'en']])
                ->end()
                ->arrayNode('content_types')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('class')->isRequired()->end()
                        ->end()
                    ->end()
                    ->defaultValue(
                        [
                            ['name' => 'Text' , 'class' =>  'Networking\InitCmsBundle\Entity\Text'],
                            ['name' => 'Gallery' , 'class' =>  'Networking\InitCmsBundle\Entity\GalleryView'],
                        ]
                    )
                ->end()
                ->arrayNode('templates')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('key')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('template')->isRequired()->end()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('icon')->end()
                            ->scalarNode('controller')->defaultValue('Networking\InitCmsBundle\Controller\FrontendPageController::indexAction')->end()
                            ->arrayNode('zones')
                            ->requiresAtLeastOneElement()
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')->isRequired()->end()
                                        ->scalarNode('class')->defaultValue('col-md-12')->end()
                                        ->scalarNode('max_content_items')->defaultValue(false)->end()
                                        ->arrayNode('restricted_types')->prototype('scalar')->end()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('google_authenticator')
                    ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('server')->cannotBeEmpty()->end()
                            ->scalarNode('enabled')->defaultFalse()->end()
                            ->arrayNode('ip_white_list')
                            ->prototype('scalar')->end()
                            ->info('IPs for which 2FA will be skipped.')
                            ->setDeprecated('The "%node%" option is deprecated. Use "trusted_ip_list" instead with the same values.')
                            ->end()
                            ->arrayNode('trusted_ip_list')
                            ->prototype('scalar')->end()
                            ->defaultValue(['127.0.0.1'])
                            ->info('IPs for which 2FA will be skipped.')
                            ->end()
                            ->arrayNode('forced_for_role')
                            ->prototype('scalar')->end()
                            ->defaultValue(['ROLE_ADMIN'])
                            ->info('User roles for which 2FA is necessary.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('activate')->defaultValue(false)->end()
                            ->scalarNode('cache_time')->defaultValue('86400')->end()
                            ->scalarNode('cache_service_class')
                                ->defaultValue(false)
                                ->setDeprecated('The "%node%" option is deprecated. Use "cache_service" instead.')
                            ->end()
                            ->scalarNode('cache_service')->defaultValue('Networking\InitCmsBundle\Cache\PageCache')->end()
                        ->end()
                ->end()
                ->arrayNode('xml_sitemap')
                    ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('sitemap_url')->defaultValue('')->end()
                                ->arrayNode('additional_links')
                                ->prototype('array')
                                ->children()
                                    ->scalarNode('locale')->defaultValue('')->end()
                                    ->arrayNode('links')->prototype('scalar')->end()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
            ->end();

        $this->addEditableSection($rootNode);
        return $treeBuilder;
    }

    protected function addEditableSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('translation_admin')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('defaultDomain')->defaultValue('messages')->end()
                        ->arrayNode('defaultSelections')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('nonTranslatedOnly')->defaultFalse()->end()
                            ->end()
                        ->end()
                        ->arrayNode('emptyPrefixes')
                            ->defaultValue(array('__', 'new_', ''))
                            ->prototype('array')->end()
                        ->end()
                        ->arrayNode('editable')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('mode')->defaultValue('popup')->end()
                                ->scalarNode('type')->defaultValue('textarea')->end()
                                ->scalarNode('emptytext')->defaultValue('Empty')->end()
                                ->scalarNode('placement')->defaultValue('top')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
