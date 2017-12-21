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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class OverrideServiceCompilerPass
 * @package Networking\InitCmsBundle\DependencyInjection\Compiler
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

            $definition->setClass('Networking\InitCmsBundle\Builder\ListBuilder');
        }

        if ($container->hasDefinition('sonata.user.editable_role_builder')) {
            $definition = $container->getDefinition('sonata.user.editable_role_builder');

            $definition->setClass('Networking\InitCmsBundle\Security\EditableRolesBuilder');
        }

        if ($container->hasDefinition('sonata.admin.builder.mongodb_list')) {
            $definition = $container->getDefinition('sonata.admin.builder.mongodb_list');

            $definition->setClass('Networking\InitCmsBundle\Builder\MongoDBListBuilder');
        }

        if ($container->hasDefinition('sonata.media.provider.youtube')) {
            $definition = $container->getDefinition('sonata.media.provider.youtube');

            $definition->setClass('Networking\InitCmsBundle\Provider\YouTubeProvider');
        }

        if ($container->has('sonata.media.admin.gallery')){
            $galleryAdmin = $container->getDefinition('sonata.media.admin.gallery');
            $templates = [
                'list' => 'NetworkingInitCmsBundle:GalleryAdmin:list.html.twig',
                'edit' => 'NetworkingInitCmsBundle:GalleryAdmin:edit.html.twig',
                'show' => 'NetworkingInitCmsBundle:CRUD:show.html.twig',
                'ajax' => 'NetworkingInitCmsBundle::ajax_layout.html.twig'
            ];
            foreach ($templates as $key =>  $template){
                $galleryAdmin->addMethodCall('setTemplate', [$key, $template]);
            }
        }

        $definition = $container->getDefinition('sonata.admin.pool');
        $definition->setClass('Networking\InitCmsBundle\Admin\Pool');
    }
}
