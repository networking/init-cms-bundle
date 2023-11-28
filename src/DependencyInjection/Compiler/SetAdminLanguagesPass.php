<?php

namespace Networking\InitCmsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SetAdminLanguagesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->getDefinition('sonata.user.admin.user')
            ->addMethodCall('setLanguages', [$container->getParameter('networking_init_cms.page.languages')]);
    }

}