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

        $definition = $container->getDefinition('sonata.admin.pool');
        $definition->setClass('Networking\InitCmsBundle\Admin\Pool');
    }
}
