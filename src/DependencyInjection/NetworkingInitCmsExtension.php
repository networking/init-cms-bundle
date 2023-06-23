<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Networking\InitCmsBundle\DependencyInjection;

use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Networking\InitCmsBundle\Entity\BaseUser;
use Networking\InitCmsBundle\Entity\Group;
use Networking\InitCmsBundle\EventSubscriber\AdminToolbarSubscriber;
use Sonata\Doctrine\Mapper\Builder\OptionsBuilder;
use Sonata\Doctrine\Mapper\DoctrineCollector;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @author net working AG <info@networking.ch>
 */
class NetworkingInitCmsExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');

        $configs = $container->getExtensionConfig($this->getAlias());
        $isLanguageSet = false;
        $isCacheActive = false;
        foreach ($configs as $config) {
            if (isset($config['languages'])) {
                $isLanguageSet = true;
            }
            if (isset($config['cache']) && $config['cache']['activate'] === true) {
                $isCacheActive = true;
            }
        }

        if (!$isLanguageSet) {
            $config = [
                'languages' => [
                    [
                        'label' => 'English',
                        'short_label' => '%env(LOCALE)%',
                        'locale' => '%env(LOCALE)%',
                    ],
                ],
            ];
            $container->prependExtensionConfig($this->getAlias(), $config);
        }

        if (isset($bundles['LexikTranslationBundle'])) {
            $configs = $container->getExtensionConfig('lexik_translation');
            $fallbackLocaleSet = $managedLocalesSet = false;
            foreach ($configs as $config) {
                if (isset($config['fallback_locale'])) {
                    $fallbackLocaleSet = true;
                }
                if (isset($config['managed_locales'])) {
                    $managedLocalesSet = true;
                }
            }

            if (!$fallbackLocaleSet) {
                $config = ['fallback_locale' => '%env(LOCALE)%'];
                $container->prependExtensionConfig('lexik_translation', $config);
            }

            if (!$managedLocalesSet) {
                $config = ['managed_locales' => [$_ENV['LOCALE']]];
                $container->prependExtensionConfig('lexik_translation', $config);
            }

        }

        if (isset($bundles['FrameworkBundle'])) {
            $configs = $container->getExtensionConfig('framework');
            $templatingEnginesSet = false;
            $pageCacheSet = false;
            $pools = [];
            foreach ($configs as $config) {
                if (isset($config['templating'])) {
                    if (isset($config['templating']['engines'])) {
                        $templatingEnginesSet = true;
                    }
                }

                if (isset($config['cache'])) {

                    if (isset($config['cache']['pools'])) {
                        $pools = array_merge($pools, $config['cache']['pools']);
                    }
                    if (isset($pools['page.cache'])) {
                        $pageCacheSet = true;
                    }

                }
            }

            if ($isCacheActive && !$pageCacheSet) {
                $pools['page.cache'] = ['adapter' => 'cache.app'];
                $config = ['cache' => ['pools' => $pools]];
                $container->prependExtensionConfig('framework', $config);
            }

        }

    }
    /**
     *
     * @throws \ReflectionException|\Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
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
        $loader->load('google_authenticator.xml');

        //mongodb is not yet fully supported but will come (eventually)
        if ('custom' !== $config['db_driver']) {
            $loader->load(sprintf('doctrine_%s.xml', $config['db_driver']));
            $loader->load(sprintf('ext_admin_%s.xml', $config['db_driver']));
            $loader->load(sprintf('admin_%s.xml', $config['db_driver']));
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('event_listeners.yaml');

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
            $config['class']['media'] = $container->getParameter('sonata.media.media.class');
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

        $this->configureGoogleAuthenticator($config, $container);

        $this->registerContainerParametersRecursive($container, $this->getAlias(), $config['translation_admin']);
    }

    public function configureCache($config, ContainerBuilder $container)
    {
        $container->setParameter('networking_init_cms.cache.activate', $config['activate']);
        $container->setParameter('networking_init_cms.cache.cache_time', $config['cache_time']);

        $cacheService = false;

        if (array_key_exists('cache_service', $config)) {
            $cacheService = $config['cache_service'];
        }

        if ($cacheService) {
            $reflectionClass = new \ReflectionClass($cacheService);

            if (in_array(\Networking\InitCmsBundle\Cache\PageCacheInterface::class, $reflectionClass->getInterfaceNames())) {
                $container->setParameter('networking_init_cms.page_cache_service', $cacheService);
            } else {
                throw new \RuntimeException(
                    sprintf('Cache class should implement %s interface', PageCacheInterface::class)
                );
            }
        }


    }

    /**
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
            $path = [];
            for ($i = 0; $i <= $iterator->getDepth(); $i++) {
                $path[] = $iterator->getSubIterator($i)->key();
            }
            $key = $alias.'.'.implode(".", $path);
            $container->setParameter($key, $value);
        }
    }

    /**
     * @param $config
     */
    public function configureLanguageCookie($config, ContainerBuilder $container)
    {
        $container->setParameter('networking_init_cms.single_language', $config['single_language']);

        $container->setParameter('networking_init_cms.allow_locale_cookie', $config['allow_locale_cookie']);
    }

    /**
     * @param $config
     *
     * @throws \InvalidArgumentException
     */
    public function configureClass($config, ContainerBuilder $container)
    {
        // admin configuration
        $container->setParameter('networking_init_cms.admin.page.class', $config['class']['page']);
        $container->setParameter('networking_init_cms.admin.layout_block.class', $config['class']['layout_block']);
        $container->setParameter('networking_init_cms.admin.user.class', $config['class']['user']);
        $container->setParameter('networking_init_cms.admin.menu_item.class', $config['class']['menu_item']);

        // manager configuration
        $container->setParameter('networking_init_cms.manager.page.class', $config['class']['page']);
        $container->setParameter('networking_init_cms.manager.layout_block.class', $config['class']['layout_block']);
        $container->setParameter('networking_init_cms.manager.user.class', $config['class']['user']);
        $container->setParameter('networking_init_cms.manager.menu_item.class', $config['class']['menu_item']);

       $container->setParameter('networking_init_cms.admin.layout_block.class',\Networking\InitCmsBundle\Entity\LayoutBlock::class);
    }



    /**
     * @param array $config
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function configureGoogleAuthenticator($config, ContainerBuilder $container)
    {
        $container->setParameter('networking_init_cms.google.authenticator.enabled', $config['google_authenticator']['enabled']);

        if (!$config['google_authenticator']['enabled']) {
            $container->removeDefinition('networking_init_cms.google.authenticator');
            $container->removeDefinition('networking_init_cms.google.authenticator.success_handler');
            $container->removeDefinition('networking_init_cms.google.authenticator.helper');
            $container->removeDefinition('networking_init_cms.google.authenticator.interactive_login_listener');
            $container->removeDefinition('networking_init_cms.google.authenticator.request_listener');

            return;
        }

        if (!class_exists('Google\Authenticator\GoogleAuthenticator')
            && !class_exists(\Sonata\GoogleAuthenticator\GoogleAuthenticator::class)) {
            throw new \RuntimeException('Please add "sonata-project/google-authenticator" package');
        }

        $container->setParameter('networking_init_cms.google.authenticator.forced_for_role', $config['google_authenticator']['forced_for_role']);

        $trustedIpList = $config['google_authenticator']['trusted_ip_list'];
        if (array_key_exists('ip_white_list', $config['google_authenticator']) && (is_countable($config['google_authenticator']['ip_white_list']) ? \count($config['google_authenticator']['ip_white_list']) : 0) > 0) {
            $trustedIpList = $config['google_authenticator']['ip_white_list'];
        }
        // NEXT_MAJOR: Remove `networking_init_cms.google.authenticator.ip_white_list` parameter.
        $container->setParameter('networking_init_cms.google.authenticator.ip_white_list', $trustedIpList);
        $container->setParameter('networking_init_cms.google.authenticator.trusted_ip_list', $trustedIpList);
        $container->getDefinition('networking_init_cms.google.authenticator.helper')
            ->replaceArgument(0, $config['google_authenticator']['server']);
        $container->setAlias( \Networking\InitCmsBundle\GoogleAuthenticator\HelperInterface::class, 'networking_init_cms.google.authenticator.helper');

    }


    /**
     * Add short labels for languages (e.g. de_CH becomes DE).
     *
     *
     * @return array
     */
    protected function addShortLabels(array $languages)
    {
        foreach ($languages as $key => $val) {
            if (!array_key_exists('short_label', $val) || !$val['short_label']) {
                $languages[$key]['short_label'] = substr(strtoupper((string) $val['label']), 0, 2);
            }
        }

        return $languages;
    }

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
            OptionsBuilder::createManyToMany('translations', $config['class']['page'])
                ->mappedBy('originals')
        );


        $collector->addAssociation(
            $config['class']['page'],
            'mapManyToMany',
            OptionsBuilder::createManyToMany('originals', $config['class']['page'])
                ->inversedBy('translations')
                ->cascade(['persist'])
                ->addJoinTable(
                    'page_translation',
                    ['name' => 'translation_id', 'referencedColumnName' => 'id',],
                    ['name' => 'original_id', 'referencedColumnName' => 'id',]
                )
        );

        //LayoutBlock

        $collector->addAssociation(
            $baseNameSpace.'\\LayoutBlock',
            'mapManyToOne',
            OptionsBuilder::createManyToOne('page', $config['class']['page'])
                ->inversedBy('layoutBlock')
                ->cascade(['persist', 'detach'])
                ->addJoin([
                    'name' => 'page_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ])

        );

        //MenuItem
        $collector->addAssociation(
            $config['class']['menu_item'],
            'mapManyToOne',
            OptionsBuilder::createManyToOne('page', $config['class']['page'])
                ->inversedBy('menuItem')
                ->cascade(['persist'])
                ->addJoin([
                    'name' => 'page_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'SET NULL',
                    'nullable' => 'true',
                ])
        );

        //PageSnapshot
        $collector->addAssociation(
            $baseNameSpace.'\\PageSnapshot',
            'mapManyToOne',
            OptionsBuilder::createManyToOne('page', $config['class']['page'])
                ->inversedBy('snapshots')
                ->cascade(['persist'])
        );

        //Social media Media item
        $collector->addAssociation(
            $config['class']['page'],
            'mapManyToOne',
            OptionsBuilder::createManyToOne('socialMediaImage', $config['class']['media'])
                ->addJoin([
                    'name' => 'social_media_image_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'SET NULL',
                    'nullable' => 'true',
                ])
        );



        $collector->addAssociation(
            BaseUser::class,
            'mapManyToMany',
            OptionsBuilder::createManyToMany('groups', Group::class)
                ->mappedBy('groups')
                ->addJoinTable('user_user_group', [
                    [
                        'name' => 'user_id',
                        'referencedColumnName' => 'id',
                        'onDelete' => 'CASCADE',
                    ],
                ], [
                    [
                        'name' => 'group_id',
                        'referencedColumnName' => 'id',
                        'onDelete' => 'CASCADE',
                    ],
                ])
        );
    }
}
