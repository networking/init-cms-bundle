<?php
namespace Networking\InitCmsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('sonata.admin.builder.orm_list');
        $definition->setClass('Networking\InitCmsBundle\Builder\ListBuilder');
    }
}
