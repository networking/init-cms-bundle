<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/doctrine.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/fos_ck_editor.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/oneup_flysystem.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/oneup_uploader.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/sonata_admin.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/sonata_block.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/sonata_doctrine_admin.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/sonata_media.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/sonata_user.php');
    $container->import('@NetworkingInitCmsBundle/Resources/config/cms/symfony_cmf_routing_extra.php');

    $container->extension('networking_init_cms', [
        'class' => [
            'page' => 'App\\Entity\\Page',
            'user' => 'App\\Entity\\User',
        ],
        'templates' => [
            'app_single_column' => [
                'template' => '@NetworkingInitCms/sandbox/page/one_column.html.twig',
                'name' => 'Single Column',
                'icon' => 'bundles/networkinginitcms/img/template_header_one_column.png',
                'zones' => [
                    ['name' => 'header', 'class' => 'col-md-12'],
                    ['name' => 'main_content', 'class' => 'col-md-12'],
                ],
            ],
            'app_two_column' => [
                'template' => '@NetworkingInitCms/sandbox/page/two_column.html.twig',
                'name' => 'Two Column',
                'icon' => 'bundles/networkinginitcms/img/template_header_two_column.png',
                'zones' => [
                    ['name' => 'header', 'class' => 'col-md-12'],
                    ['name' => 'left', 'class' => 'col-md-3'],
                    ['name' => 'right', 'class' => 'col-md-9'],
                ],
            ],
        ],
        'cache' => [
            'activate' => true,
        ],
    ]);
};
