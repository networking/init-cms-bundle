<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('sonata_admin', [
        'persist_filters' => true,
        'options' => [
            'pager_links' => 10,
            'default_admin_route' => 'edit',
        ],
        'global_search' => [
            'admin_route' => 'edit',
        ],
        'security' => [
            'handler' => 'sonata.admin.security.handler.acl',
            'information' => [
                'GUEST' => ['VIEW', 'LIST'],
                'STAFF' => ['EDIT', 'LIST', 'CREATE'],
                'EDITOR' => ['OPERATOR', 'EXPORT'],
                'ADMIN' => ['MASTER', 'PUBLISH'],
                'PUBLISHER' => ['PUBLISH', 'EDIT', 'LIST', 'CREATE'],
            ],
            'admin_permissions' => ['CREATE', 'LIST', 'DELETE', 'UNDELETE', 'EXPORT', 'OPERATOR', 'MASTER', 'PUBLISH'],
            'object_permissions' => ['VIEW', 'EDIT', 'DELETE', 'UNDELETE', 'OPERATOR', 'MASTER', 'OWNER', 'PUBLISH'],
        ],
        'title' => 'Networking CMS',
        'title_logo' => '/bundles/networkinginitcms/img/initcms-396x100.png',
        'templates' => [
            'knp_menu_template' => '@NetworkingInitCms/Menu/admin_side_menu.html.twig',
            'user_block' => '@NetworkingInitCms/Admin/user_block.html.twig',
            'layout' => '@NetworkingInitCms/admin_layout.html.twig',
            'ajax' => '@NetworkingInitCms/ajax_layout.html.twig',
            'search' => '@NetworkingInitCms/Core/search.html.twig',
            'list' => '@NetworkingInitCms/CRUD/list.html.twig',
            'show' => '@NetworkingInitCms/CRUD/show.html.twig',
            'edit' => '@NetworkingInitCms/CRUD/edit.html.twig',
            'preview' => '@NetworkingInitCms/CRUD/preview.html.twig',
            'delete' => '@NetworkingInitCms/CRUD/delete.html.twig',
            'action' => '@NetworkingInitCms/CRUD/action.html.twig',
            'select' => '@NetworkingInitCms/CRUD/list__select.html.twig',
            'dashboard' => '@NetworkingInitCms/Core/dashboard.html.twig',
            'batch_confirmation' => '@NetworkingInitCms/CRUD/batch_confirmation.html.twig',
            'base_list_field' => '@NetworkingInitCms/CRUD/base_list_field.html.twig',
            'inner_list_row' => '@NetworkingInitCms/CRUD/list_inner_row.html.twig',
            'batch' => '@NetworkingInitCms/CRUD/list__batch.html.twig',
            'short_object_description' => '@NetworkingInitCms/Helper/short-object-description.html.twig',
            'list_block' => '@NetworkingInitCms/Block/block_admin_list.html.twig',
            'form_theme' => ['@NetworkingInitCms/Form/form_admin_fields.html.twig'],
            'filter_theme' => ['@NetworkingInitCms/Form/form_admin_fields.html.twig'],
            'search_result_block' => '@NetworkingInitCms/Block/block_search_result.html.twig',
        ],
        'dashboard' => [
            'blocks' => [
                ['position' => 'top', 'type' => 'networking_init_cms.block.service.translatable_text', 'settings' => ['translation_key' => 'info.welcome_text', 'translation_domain' => 'NetworkingInitCmsBundle']],
                ['position' => 'left', 'type' => 'networking_init_cms.block.service.pages'],
                ['position' => 'right', 'type' => 'networking_init_cms.block.service.online_users'],
                ['position' => 'right', 'type' => 'networking_init_cms.block.service.cache'],
                ['position' => 'right', 'type' => 'networking_init_cms.block.service.sitemap'],
            ],
            'groups' => [
                'dashboard' => [
                    'on_top' => true,
                    'label' => 'admin.menu.dashboard',
                    'translation_domain' => 'PageAdmin',
                    'icon' => 'ki-outline ki-element-11 fs-2',
                    'items' => [
                        ['route' => 'sonata_admin_dashboard', 'label' => 'admin.menu.dashboard'],
                    ],
                ],
                'pages' => [
                    'on_top' => true,
                    'label' => 'pages.admin_menu_label',
                    'translation_domain' => 'PageAdmin',
                    'icon' => 'ki-outline ki-element-7 fs-2',
                    'items' => ['networking_init_cms.admin.page'],
                ],
                'menu' => [
                    'on_top' => true,
                    'label' => 'menu.admin_menu_label',
                    'translation_domain' => 'MenuItemAdmin',
                    'icon' => 'ki-outline ki-text-align-left fs-2',
                    'items' => ['networking_init_cms.admin.menu_item'],
                ],
                'media' => [
                    'label' => 'sonata_media.admin_menu_label',
                    'translation_domain' => 'MediaAdmin',
                    'icon' => 'ki-outline ki-picture fs-2',
                    'items' => ['sonata.media.admin.media', 'sonata.media.admin.gallery', 'networking_init_cms.admin.tag'],
                ],
                'users' => [
                    'label' => 'sonata_user',
                    'translation_domain' => 'SonataUserBundle',
                    'icon' => 'ki-outline ki-address-book fs-2',
                    'items' => ['sonata.user.admin.user', 'networking_init_cms.admin.group'],
                ],
                'help_text' => [
                    'on_top' => true,
                    'label' => 'help_text.admin_menu_label',
                    'translation_domain' => 'HelpTextAdmin',
                    'icon' => 'ki-outline ki-question-2 fs-2',
                    'items' => ['networking_init_cms.admin.help_text'],
                ],
                'translation' => [
                    'on_top' => true,
                    'label' => 'translation.admin_menu_label',
                    'translation_domain' => 'TranslationAdmin',
                    'icon' => 'la la-language fs-2',
                    'items' => ['networking_init_cms.admin_entity.translation_admin'],
                ],
            ],
        ],
    ]);
};
