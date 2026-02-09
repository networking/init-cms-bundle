<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('framework', [
        'assets' => [
            'packages' => [
                'init_cms' => [
                    'base_path' => '/bundles/networkinginitcms',
                    'json_manifest_path' => '%kernel.project_dir%/public/bundles/networkinginitcms/manifest.json',
                ],
            ],
        ],
    ]);
};
