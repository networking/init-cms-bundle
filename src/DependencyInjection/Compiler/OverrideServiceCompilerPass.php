<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\DependencyInjection\Compiler;

use Networking\InitCmsBundle\Admin\Pool;
use Networking\InitCmsBundle\Builder\ListBuilder;
use Networking\InitCmsBundle\Provider\YouTubeProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class OverrideServiceCompilerPass.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class OverrideServiceCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('sonata.admin.builder.orm_list')) {
            $definition = $container->getDefinition('sonata.admin.builder.orm_list');
            $definition->setClass(ListBuilder::class);
        }

        if ($container->hasDefinition('sonata.media.provider.youtube')) {
            $definition = $container->getDefinition('sonata.media.provider.youtube');
            $definition->setClass(YouTubeProvider::class);
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

        $definition = $container->getDefinition('sonata.admin.pool');
        $definition->setClass(Pool::class);
    }
}
