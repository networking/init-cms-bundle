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

namespace Networking\InitCmsBundle\DependencyInjection\Compiler;

use Networking\InitCmsBundle\Builder\ListBuilder;
use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class OverrideServiceCompilerPass.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('sonata.admin.builder.orm_list')) {
            $definition = $container->getDefinition('sonata.admin.builder.orm_list');
            $definition->setClass(ListBuilder::class);
        }

        if ($container->has('sonata.media.admin.gallery')) {
            $galleryAdmin = $container->getDefinition('sonata.media.admin.gallery');
            $templates = [
                'list' => '@NetworkingInitCms/GalleryAdmin/list.html.twig',
                'edit' => '@NetworkingInitCms/GalleryAdmin/edit.html.twig',
                'show' => '@NetworkingInitCms/CRUD/show.html.twig',
                'ajax' => '@NetworkingInitCms/ajax_layout.html.twig',
            ];
            foreach ($templates as $key => $template) {
                $galleryAdmin->addMethodCall('setTemplate', [$key, $template]);
            }
        }

        if ($container->hasParameter('networking_init_cms.page_cache_service')) {
            $service = $container->getParameter('networking_init_cms.page_cache_service');
            $definition = $container->getDefinition($service);
            $container->setDefinition(PageCacheInterface::class, $definition);
        }

        if ($container->hasDefinition('sonata.user.admin.user')) {
            $definition = $container->getDefinition('sonata.user.admin.user');
            $definition->addMethodCall('setTokenStorage', [$container->getDefinition('security.token_storage')]);
            if ($container->getParameter('networking_init_cms.google.authenticator.enabled') && $container->hasDefinition('networking_init_cms.google.authenticator.helper')) {
                $definition->addMethodCall('setGoogleAuthEnabled', [$container->getParameter('networking_init_cms.google.authenticator.enabled')]);
            }
        }
    }
}
