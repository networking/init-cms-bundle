<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('lexik_translation', [
        'fallback_locale' => '%env(LOCALE)%',
        'managed_locales' => ['%env(LOCALE)%'],
        'resources_registration' => [
            'type' => 'all',
            'managed_locales_only' => false,
        ],
    ]);
};
