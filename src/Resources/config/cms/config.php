<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/assets.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/doctrine.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/fos_ck_editor.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/lexik_translation.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/oneup_flysystem.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/oneup_uploader.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/security.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/sonata_admin.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/sonata_block.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/sonata_doctrine_admin.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/sonata_media.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/sonata_user.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/symfony_cmf_routing_extra.php');
};
