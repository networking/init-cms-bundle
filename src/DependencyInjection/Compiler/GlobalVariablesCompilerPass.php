<?php

declare(strict_types=1);


namespace Networking\InitCmsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;


final class GlobalVariablesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->getDefinition('twig')
            ->addMethodCall('addGlobal', ['webauthn_enabled', $container->getParameter('networking_init_cms.webauthn.enabled')])
            ->addMethodCall('addGlobal', ['google_authenticator_enabled', $container->getParameter('networking_init_cms.google.authenticator.enabled')]);
    }
}
