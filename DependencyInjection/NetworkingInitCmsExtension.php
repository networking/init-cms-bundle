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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;
use Sonata\AdminBundle\DependencyInjection\SonataAdminExtension;

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
        $defaults = Yaml::parse(__DIR__ . '/../Resources/config/config.yml');

        foreach ($configs as $config) {
            foreach ($config as $key => $value) {
                $defaults['networking_init_cms'][$key] = $value;

            }
        }

        $config = $this->processConfiguration($configuration, $defaults);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $config['languages'] = $this->addShortLabels($config['languages']);

        $container->setParameter('networking_init_cms.page.languages', $config['languages']);
        $container->setParameter('networking_init_cms.page.templates', $config['templates']);
        $container->setParameter('networking_init_cms.page.content_types', $config['content_types']);


    }

    protected function addShortLabels(array $languages){
        foreach ( $languages as $key => $val){
            if(!array_key_exists('short_label', $val) || !$val['short_label']){
                $languages[$key]['short_label'] = substr(strtoupper($val['label']), 0, 2);
            }
        }

        return $languages;
    }
}
