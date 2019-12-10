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

use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Networking\InitCmsBundle\EventSubscriber\AdminToolbarSubscriber;
use Networking\InitCmsBundle\Lib\PhpCacheInterface;
use Sonata\CoreBundle\Exception\InvalidParameterException;
use Sonata\EasyExtendsBundle\Mapper\DoctrineCollector;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @author net working AG <info@networking.ch>
 */
class NetworkingInitCmsExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        $configs = $container->getExtensionConfig($this->getAlias());
        $isLanguageSet = false;
        $isCacheActive = false;
        foreach ($configs as $config){
            if(isset($config['languages'])){
                $isLanguageSet = true;
            }
            if(isset($config['cache']) && $config['cache']['activate'] === true){
                $isCacheActive = true;
            }
        }

        if(!$isLanguageSet){
            $config = ['languages' => [['label' => 'English', 'short_label' => '%env(LOCALE)%', 'locale' => '%env(LOCALE)%']]];
            $container->prependExtensionConfig($this->getAlias(), $config);
        }

        if(isset($bundles['LexikTranslationBundle'])){
            $configs = $container->getExtensionConfig('lexik_translation');
            $fallbackLocaleSet = $managedLocalesSet = false;
            foreach ($configs as $config){
                if(isset($config['fallback_locale'])){
                    $fallbackLocaleSet = true;
                }
                if(isset($config['managed_locales'])){
                    $managedLocalesSet = true;
                }
            }

            if(!$fallbackLocaleSet){
                $config = ['fallback_locale' => '%env(LOCALE)%'];
                $container->prependExtensionConfig('lexik_translation', $config);
            }

            if(!$managedLocalesSet){
                $config = ['managed_locales' => [$_ENV['LOCALE']]];
                $container->prependExtensionConfig('lexik_translation', $config);
            }

        }

        if(isset($bundles['FrameworkBundle'])){
            $configs = $container->getExtensionConfig('framework');
            $templatingEnginesSet =  false;
            $pageCacheSet = false;
            $pools = [];
            foreach ($configs as $config){
                if(isset($config['templating'])){
                    if(isset($config['templating']['engines'])){
                        $templatingEnginesSet = true;
                    }
                }

                if(isset($config['cache'])){

                    if(isset($config['cache']['pools'])){
                        $pools = array_merge($pools, $config['cache']['pools']);
                    }
                    if(isset($pools['page.cache'] )){
                        $pageCacheSet = true;
                    }

                }
            }

            if(!$templatingEnginesSet){
                $engines = [];
                if(class_exists('Symfony\Bundle\TwigBundle\TwigEngine')){
                    $engines[] = 'twig';
                }elseif(class_exists('Symfony\Component\Templating\PhpEngine')){
                    $engines[] = 'php';
                }

                $config = ['templating' => ['engines' => $engines]];
                $container->prependExtensionConfig('framework', $config);
            }

            if($isCacheActive && !$pageCacheSet){
                $pools['page.cache'] = ['adapter' => 'cache.app'];
                $config = ['cache' => ['pools' => $pools]];
                $container->prependExtensionConfig('framework', $config);
            }

        }

    }

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     *
     * @throws \ReflectionException|\Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('blocks.xml');
        $loader->load('event_listeners.xml');
        $loader->load('forms.xml');
        $loader->load('menus.xml');
        $loader->load('twig.xml');
        $loader->load('services.xml');
        $loader->load('validators.xml');

        //mongodb is not yet fully supported but will come (eventually)
        if ('custom' !== $config['db_driver']) {
            $loader->load(sprintf('doctrine_%s.xml', $config['db_driver']));
            $loader->load(sprintf('ext_admin_%s.xml', $config['db_driver']));
            $loader->load(sprintf('admin_%s.xml', $config['db_driver']));
        }

        $config['languages'] = $this->addShortLabels($config['languages']);

        $container->setParameter('networking_init_cms.page.languages', $config['languages']);
        $container->setParameter('networking_init_cms.page.templates', $config['templates']);
        $container->setParameter('networking_init_cms.page.content_types', $config['content_types']);
        $container->setParameter(
            'networking_init_cms.translation_fallback_route',
            $config['translation_fallback_route']
        );
        $container->setParameter(
            'networking_init_cms.multiple_media_tags',
            $config['multiple_media_tags']
        );
        $container->setParameter(
            'networking_init_cms.show_tag_tree',
            $config['show_tag_tree']
        );

        if (!$config['admin_toolbar']['toolbar']) {
            $mode = AdminToolbarSubscriber::DISABLED;
        } else {
            $mode = AdminToolbarSubscriber::ENABLED;
        }
        $container->setParameter('networking_init_cms.admin_toolbar.mode', $mode);
        $container->setParameter('networking_init_cms.admin_toolbar.position', $config['admin_toolbar']['position']);

        $container->setParameter('networking_init_cms.404_template', $config['404_template']);
        $container->setParameter('networking_init_cms.no_translation_template', $config['no_translation_template']);
        $container->setParameter('networking_init_cms.db_driver', $config['db_driver']);

        if ($config['db_driver'] == 'orm') {
            $this->registerDoctrineORMMapping($config);
        }

        $container->setParameter('networking_init_cms.xml_sitemap.sitemap_url', $config['xml_sitemap']['sitemap_url']);
        $container->setParameter(
            'networking_init_cms.xml_sitemap.additional_links',
            $config['xml_sitemap']['additional_links']
        );

        $this->configureCache($config['cache'], $container);

        $this->configureLanguageCookie($config, $container);

        $this->configureClass($config, $container);

        $this->registerContainerParametersRecursive($container, $this->getAlias(), $config['translation_admin']);
    }

    public function configureCache($config, ContainerBuilder $container)
    {
        $container->setParameter('networking_init_cms.cache.activate', $config['activate']);
        $container->setParameter('networking_init_cms.cache.cache_time', $config['cache_time']);

        $cacheService = false;

        if (array_key_exists('cache_service_class', $config)) {
            $cacheService = $config['cache_service_class'];
        }

        if (array_key_exists('cache_service', $config)) {
            $cacheService = $config['cache_service'];
        }

        if ($cacheService) {
            $reflectionClass = new \ReflectionClass($cacheService);

            if (in_array('Networking\InitCmsBundle\Cache\PageCacheInterface', $reflectionClass->getInterfaceNames())) {
                $container->setParameter('networking_init_cms.page_cache_service', $cacheService);

                if (in_array('Networking\InitCmsBundle\Lib\PhpCacheInterface', $reflectionClass->getInterfaceNames())) {
                    @trigger_error(sprintf('The "%s" interface is deprecated since InitCms 4.0.2, use "%s"  instead.', PhpCacheInterface::class,  PageCacheInterface::class), E_USER_DEPRECATED);
                }
            } else {
                throw new \RuntimeException(sprintf('Cache class should implement %s interface', PageCacheInterface::class));
            }
        }


    }

    /**
     * @param ContainerBuilder $container
     * @param String $alias
     * @param array $config
     */
    protected function registerContainerParametersRecursive(ContainerBuilder $container, $alias, $config)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($config),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $value) {
            $path = array();
            for ($i = 0; $i <= $iterator->getDepth(); $i++) {
                $path[] = $iterator->getSubIterator($i)->key();
            }
            $key = $alias.'.'.implode(".", $path);
            $container->setParameter($key, $value);
        }
    }

    /**
     * @param $config
     * @param ContainerBuilder $container
     */
    public function configureLanguageCookie($config, ContainerBuilder $container)
    {
        $container->setParameter('networking_init_cms.single_language', $config['single_language']);

        $config['allow_locale_cookie'] = $config['single_language'] ? false : $config['allow_locale_cookie'];

        $container->setParameter('networking_init_cms.allow_locale_cookie', $config['allow_locale_cookie']);
    }

    /**
     * @param $config
     * @param ContainerBuilder $container
     *
     * @throws \InvalidArgumentException
     */
    public function configureClass($config, ContainerBuilder $container)
    {
        // admin configuration
        $container->setParameter('networking_init_cms.admin.page.class', $config['class']['page']);
        $container->setParameter('networking_init_cms.admin.layout_block.class', $config['class']['layout_block']);
        $container->setParameter('networking_init_cms.admin.user.class', $config['class']['user']);

        // manager configuration
        $container->setParameter('networking_init_cms.manager.page.class', $config['class']['page']);
        $container->setParameter('networking_init_cms.manager.layout_block.class', $config['class']['layout_block']);
        $container->setParameter('networking_init_cms.manager.user.class', $config['class']['user']);

        switch ($config['db_driver']) {
            case 'orm':
                $container->setParameter(
                    'networking_init_cms.admin.layout_block.class',
                    'Networking\InitCmsBundle\Entity\LayoutBlock'
                );
                break;
            case 'mongodb':
                $container->setParameter(
                    'networking_init_cms.admin.layout_block.class',
                    'Networking\InitCmsBundle\Docment\LayoutBlock'
                );
                break;
            default:
                throw new \InvalidArgumentException('db driver must be either orm or mongodb');
                break;
        }
    }

    /**
     * Add short labels for languages (e.g. de_CH becomes DE).
     *
     * @param array $languages
     *
     * @return array
     */
    protected function addShortLabels(array $languages)
    {
        foreach ($languages as $key => $val) {
            if (!array_key_exists('short_label', $val) || !$val['short_label']) {
                $languages[$key]['short_label'] = substr(strtoupper($val['label']), 0, 2);
            }
        }

        return $languages;
    }

    /**
     * @param array $config
     */
    public function registerDoctrineORMMapping(array $config)
    {
        foreach ($config['class'] as $type => $class) {
            if (!class_exists($class)) {
                return;
            }
        }

        if ($config['db_driver'] != 'orm') {
            return;
        }

        $baseNameSpace = 'Networking\\InitCmsBundle\\Entity';

        $collector = DoctrineCollector::getInstance();

        $collector->addAssociation(
            $config['class']['page'],
            'mapManyToMany',
            [
                'fieldName' => 'translations',
                'targetEntity' => $config['class']['page'],
                'mappedBy' => 'originals',
            ]
        );

        $collector->addAssociation(
            $config['class']['page'],
            'mapManyToMany',
            [
                'fieldName' => 'originals',
                'targetEntity' => $config['class']['page'],
                'inversedBy' => 'translations',
                'cascade' => ['persist'],
                'joinTable' => [
                    'name' => 'page_translation',
                    'joinColumns' => [
                        [
                            'name' => 'translation_id',
                            'referencedColumnName' => 'id',
                        ],
                    ],
                    'inverseJoinColumns' => [
                        [
                            'name' => 'original_id',
                            'referencedColumnName' => 'id',
                        ],
                    ],
                ],
            ]
        );

        //LayoutBlock
        $collector->addAssociation(
            $baseNameSpace.'\\LayoutBlock',
            'mapManyToOne',
            [
                'fieldName' => 'page',
                'targetEntity' => $config['class']['page'],
                'inversedBy' => 'layoutBlock',
                'cascade' => ['persist', 'detach'],
                'joinColumns' => [
                    [
                        'name' => 'page_id',
                        'referencedColumnName' => 'id',
                        'onDelete' => 'CASCADE',
                    ],
                ],

            ]
        );

        //MenuItem
        $collector->addAssociation(
            $baseNameSpace.'\\MenuItem',
            'mapManyToOne',
            [
                'fieldName' => 'page',
                'targetEntity' => $config['class']['page'],
                'inversedBy' => 'menuItem',
                'cascade' => ['persist'],
                'joinColumns' => [
                    [
                        'name' => 'page_id',
                        'referencedColumnName' => 'id',
                        'onDelete' => 'SET NULL',
                        'nullable' => 'true',
                    ],
                ],

            ]
        );

        //PageSnapshot
        $collector->addAssociation(
            $baseNameSpace.'\\PageSnapshot',
            'mapManyToOne',
            [
                'fieldName' => 'page',
                'targetEntity' => $config['class']['page'],
                'inversedBy' => 'snapshots',
                'cascade' => ['persist'],

            ]
        );
    }
}
