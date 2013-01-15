<?php

namespace Networking\InitCmsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Networking\InitCmsBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;

class NetworkingInitCmsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OverrideServiceCompilerPass());
    }
}
