<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('oneup_uploader', [
        'mappings' => [
            'media_admin' => [
                'frontend' => 'custom',
                'custom_frontend' => [
                    'class' => 'Networking\\InitCmsBundle\\Controller\\OneUploader\\MediaMultiUploadController',
                    'name' => 'mediaAdminMultiUploader',
                ],
            ],
        ],
    ]);
};
