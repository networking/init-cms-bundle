<?php

namespace Networking\InitCmsBundle\DependencyInjection\Compiler;

use Networking\InitCmsBundle\Lib\ScannerFactory;
use Networking\InitCmsBundle\Provider\FileProvider;
use Networking\InitCmsBundle\Provider\ImageProvider;
use Networking\InitCmsBundle\Validator\Constraints\ClamAVValidator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ClamAVCompilerPass implements CompilerPassInterface
{

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $scanner = null;

        if(!$container->hasDefinition('sineflow.clamav.scanner')){
            return;
        }

        $scanner = $container->getDefinition('sineflow.clamav.scanner');

        $factoryServiceDefinition = $container->getDefinition('networking_init_cms.clamav_factory');

        $scanner->setFactory([$factoryServiceDefinition, 'createScanner']);


        if ($definition = $container->getDefinition('sonata.media.provider.image')) {

                $definition->addMethodCall('setScanner', [$scanner]);
        }

        if ($definition = $container->getDefinition('sonata.media.provider.file')) {
                $definition->addMethodCall('setScanner', [$scanner]);
        }

        if($definition = $container->getDefinition('networking_init_cms.clamav_validator')) {
            $definition->addMethodCall('setScanner', [$scanner]);
        }
    }
}