<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('oneup_flysystem', [
        'adapters' => [
            'default_adapter' => [
                'local' => [
                    'location' => '%kernel.cache_dir%/flysystem',
                ],
            ],
            'media' => [
                'local' => [
                    'location' => '%kernel.project_dir%/public/',
                ],
            ],
            'cache' => [
                'local' => [
                    'location' => '%kernel.project_dir%/public/media/cache/',
                ],
            ],
        ],
        'filesystems' => [
            'default_filesystem' => [
                'adapter' => 'default_adapter',
                'alias' => 'League\\Flysystem\\Filesystem',
            ],
            'media' => [
                'adapter' => 'media',
            ],
            'cache' => [
                'adapter' => 'cache',
            ],
        ],
    ]);
};
