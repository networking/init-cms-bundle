<?php
namespace Networking\InitCmsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('sonata.admin.builder.orm_list')) {
            $definition = $container->getDefinition('sonata.admin.builder.orm_list');

            $definition->setClass('Networking\InitCmsBundle\Builder\ListBuilder');
        }

        if ($container->hasDefinition('sonata.admin.builder.mongodb_list')) {
            $definition = $container->getDefinition('sonata.admin.builder.mongodb_list');

            $definition->setClass('Networking\InitCmsBundle\Builder\MongoDBListBuilder');
        }

        $definition = $container->getDefinition('sonata.admin.pool');
        $definition->setClass('Networking\InitCmsBundle\Admin\Pool');
    }
}
