<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('sonata_block', [
        'default_contexts' => ['cms'],
        'blocks' => [
            'networking_init_cms.block.service.translatable_text' => null,
            'networking_init_cms.block.service.online_users' => null,
            'networking_init_cms.block.service.pages' => null,
            'networking_init_cms.block.service.cache' => null,
            'networking_init_cms.block.service.sitemap' => null,
            'networking_init_cms.block.service.version' => null,
        ],
    ]);
};
