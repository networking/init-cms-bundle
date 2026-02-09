<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Networking\InitCmsBundle\Security\Acl\Permission\AdminPermissionMap;
use Networking\InitCmsBundle\Security\Acl\Permission\MaskBuilder;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('security.acl.permission.map', AdminPermissionMap::class);

    $container->parameters()
        ->set('sonata.admin.security.mask.builder.class', MaskBuilder::class);

    $container->extension('security', [
        'access_decision_manager' => [
            'strategy' => 'unanimous',
        ],
        'role_hierarchy' => [
            'ROLE_SUPER_ADMIN' => ['ROLE_SONATA_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'],
        ],
        'password_hashers' => [
            'Sonata\\UserBundle\\Model\\UserInterface' => 'auto',
        ],
        'providers' => [
            'sonata_user_bundle' => [
                'id' => 'sonata.user.security.user_provider',
            ],
        ],
        'firewalls' => [
            'admin' => [
                'switch_user' => true,
                'context' => 'user',
                'pattern' => '/admin(.*)',
                'form_login' => [
                    'provider' => 'sonata_user_bundle',
                    'enable_csrf' => true,
                    'login_path' => '/admin/login',
                    'use_forward' => false,
                    'check_path' => '/admin/login_check',
                    'failure_path' => null,
                    'use_referer' => true,
                    'default_target_path' => '/admin/dashboard',
                ],
                'json_login' => [
                    'provider' => 'sonata_user_bundle',
                    'check_path' => 'cms_api_login',
                    'username_path' => '_username',
                    'password_path' => '_password',
                ],
                'logout' => [
                    'path' => '/admin/logout',
                    'target' => '/',
                    'invalidate_session' => true,
                ],
                'remember_me' => [
                    'secret' => '%kernel.secret%',
                    'lifetime' => 31536000,
                    'path' => '/',
                    'domain' => null,
                ],
                'webauthn' => [
                    'registration' => [
                        'enabled' => true,
                        'routes' => [
                            'options_path' => '/admin/register/options',
                        ],
                    ],
                    'authentication' => [
                        'enabled' => true,
                        'routes' => [
                            'options_path' => '/admin/assertion/options',
                            'result_path' => '/admin/assertion',
                        ],
                    ],
                ],
            ],
            'main' => [
                'switch_user' => true,
                'context' => 'user',
                'pattern' => '.*',
                'form_login' => [
                    'provider' => 'sonata_user_bundle',
                    'enable_csrf' => true,
                    'login_path' => '/login',
                    'use_forward' => false,
                    'check_path' => '/login_check',
                    'failure_path' => null,
                ],
                'logout' => true,
            ],
        ],
        'access_control' => [
            ['path' => '^/_wdt', 'role' => 'PUBLIC_ACCESS'],
            ['path' => '^/_profiler', 'role' => 'PUBLIC_ACCESS'],
            ['path' => '^/login$', 'role' => 'PUBLIC_ACCESS'],
            ['path' => '^/login_$', 'role' => 'PUBLIC_ACCESS'],
            ['path' => '^/admin/assertion', 'roles' => 'PUBLIC_ACCESS', 'requires_channel' => 'https'],
            ['path' => '^/admin/login$', 'role' => 'PUBLIC_ACCESS'],
            ['path' => '^/admin/logout$', 'role' => 'PUBLIC_ACCESS'],
            ['path' => '^/admin/login_check$', 'role' => 'PUBLIC_ACCESS'],
            ['path' => '^/admin/request', 'role' => 'PUBLIC_ACCESS'],
            ['path' => '^/admin/send-email', 'role' => 'PUBLIC_ACCESS'],
            ['path' => '^/admin/check-email', 'role' => 'PUBLIC_ACCESS'],
            ['path' => '^/admin/reset', 'role' => 'PUBLIC_ACCESS'],
            ['path' => '^/admin/_initcms_admin_navbar/.*$', 'role' => 'PUBLIC_ACCESS'],
            ['path' => '^/admin$', 'role' => ['ROLE_SONATA_ADMIN']],
            ['path' => '^/admin/.*', 'role' => ['ROLE_SONATA_ADMIN']],
            ['path' => '^/.*', 'role' => 'PUBLIC_ACCESS'],
        ],
    ]);
};
