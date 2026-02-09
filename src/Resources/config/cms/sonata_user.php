<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('sonata_user', [
        'security_acl' => false,
        'impersonating' => [
            'route' => 'networking_init_cms_admin',
            'parameters' => ['path' => '/'],
        ],
        'class' => [
            'user' => 'App\\Entity\\User',
        ],
        'resetting' => [
            'email' => [
                'template' => '@NetworkingInitCms/Admin/Security/Resetting/email.html.twig',
                'address' => '%env(ADMIN_EMAIL_ADDRESS)%',
                'sender_name' => 'InitCms',
            ],
        ],
        'admin' => [
            'user' => [
                'class' => 'Networking\\InitCmsBundle\\Admin\\UserAdmin',
                'controller' => 'Networking\\InitCmsBundle\\Controller\\UserAdminController',
                'translation' => 'SonataUserBundle',
            ],
        ],
    ]);
};
