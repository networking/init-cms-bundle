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

use Networking\InitCmsBundle\EventListener\AdminToolbarListener;
use Sonata\CoreBundle\Exception\InvalidParameterException;
use Sonata\EasyExtendsBundle\Mapper\DoctrineCollector;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;


/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 * @author net working AG <info@networking.ch>
 */
class NetworkingInitCmsExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {

        $configuration = new Configuration();
        $defaults = Yaml::parse(__DIR__ . '/../Resources/config/cms/config.yml');

        foreach ($configs as $config) {
                    foreach ($config as $key => $value) {
                        $defaults['networking_init_cms'][$key] = $value;

                    }
                }

        $config = $this->processConfiguration($configuration, $defaults);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('blocks.xml');
        $loader->load('dynamic_routing.xml');
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


        if (!$config['admin_toolbar']['toolbar']) {
            $mode = AdminToolbarListener::DISABLED;
        } else {
            $mode = AdminToolbarListener::ENABLED;
        }
        $container->setParameter('networking_init_cms.admin_toolbar.mode', $mode);
        $container->setParameter('networking_init_cms.admin_toolbar.position', $config['admin_toolbar']['position']);

        $container->setParameter('networking_init_cms.404_template', $config['404_template']);
        $container->setParameter('networking_init_cms.no_translation_template', $config['no_translation_template']);
        $container->setParameter('networking_init_cms.admin_menu_groups', $config['admin_menu_groups']);
        $container->setParameter('networking_init_cms.db_driver', $config['db_driver']);

        if ($config['db_driver'] == 'orm') {
            $this->registerDoctrineORMMapping($config);
        }

        $container->setParameter('networking_init_cms.cache.activate', $config['cache']['activate']);
        $container->setParameter('networking_init_cms.cache.cache_time', $config['cache']['cache_time']);
        $cacheClass = $config['cache']['cache_service_class'];
        $reflectionClass = new \ReflectionClass($cacheClass);

        if(in_array('Networking\InitCmsBundle\Lib\PhpCacheInterface', $reflectionClass->getInterfaceNames())){
            $container->setParameter('networking_init_cms.lib.php_cache.class', $config['cache']['cache_service_class']);
        }else{
            throw new InvalidParameterException('Cache class should implement the PhpCacheInterface interface');
        }

        $this->configureClass($config, $container);
    }

    /**
     * @param $config
     * @param ContainerBuilder $container
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

        if($config['db_driver'] == 'mongodb'){

        }

        switch ($config['db_driver']){
            case 'orm':
                $container->setParameter('networking_init_cms.admin.layout_block.class', 'Networking\InitCmsBundle\Entity\LayoutBlock');
                break;
            case 'mongodb':
                $container->setParameter('networking_init_cms.admin.layout_block.class', 'Networking\InitCmsBundle\Docment\LayoutBlock');
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
            array(
                'fieldName' => 'translations',
                'targetEntity' => $config['class']['page'],
                'mappedBy' => 'originals',
            )
        );


        $collector->addAssociation(
            $config['class']['page'],
            'mapManyToMany',
            array(
                'fieldName' => 'originals',
                'targetEntity' => $config['class']['page'],
                'inversedBy' => "translations",
                'cascade' => array('persist'),
                'joinTable' => array(
                    'name' => 'page_translation',
                    'joinColumns' => array(
                        array(
                            'name' => 'translation_id',
                            'referencedColumnName' => 'id'
                        ),
                    ),
                    'inverseJoinColumns' => array(
                        array(
                            'name' => 'original_id',
                            'referencedColumnName' => 'id'
                        )
                    ),
                )
            )
        );


        //LayoutBlock
        $collector->addAssociation(
            $baseNameSpace . '\\LayoutBlock',
            'mapManyToOne',
            array(
                'fieldName' => 'page',
                'targetEntity' => $config['class']['page'],
                'inversedBy' => "layoutBlock",
                'cascade' => array('persist', 'detach'),
                'joinColumns' => array(
                    array(
                        'name' => 'page_id',
                        'referencedColumnName' => 'id',
                        'onDelete' => 'CASCADE'
                    )
                )

            )
        );


        //MenuItem
        $collector->addAssociation(
            $baseNameSpace . '\\MenuItem',
            'mapManyToOne',
            array(
                'fieldName' => 'page',
                'targetEntity' => $config['class']['page'],
                'inversedBy' => "menuItem",
                'cascade' => array('persist'),
                'joinColumns' => array(
                    array(
                        'name' => 'page_id',
                        'referencedColumnName' => 'id',
                        'onDelete' => 'SET NULL',
                        'nullable' => 'true'
                    )
                )

            )
        );

        //PageSnapshot
        $collector->addAssociation(
            $baseNameSpace . '\\PageSnapshot',
            'mapManyToOne',
            array(
                'fieldName' => 'page',
                'targetEntity' => $config['class']['page'],
                'inversedBy' => "snapshots",
                'cascade' => array('persist')

            )
        );
    }
}
