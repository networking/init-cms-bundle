<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->import('../gedmo_doctrine_extensions.php');

    $container->extension('doctrine', [
        'dbal' => [
            'url' => '%env(resolve:DATABASE_URL)%',
            'default_connection' => 'default',
            'charset' => 'utf8mb4',
            'default_table_options' => [
                'collate' => 'utf8mb4_unicode_ci',
            ],
        ],
        'orm' => [
            'auto_generate_proxy_classes' => true,
            'entity_managers' => [
                'default' => [
                    'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                    'auto_mapping' => true,
                    'connection' => 'default',
                    'filters' => [
                        'softdeleteable' => [
                            'class' => 'Gedmo\\SoftDeleteable\\Filter\\SoftDeleteableFilter',
                            'enabled' => true,
                        ],
                    ],
                    'mappings' => [
                        'App' => [
                            'type' => 'attribute',
                            'dir' => '%kernel.project_dir%/src/Entity',
                            'prefix' => 'App\\Entity',
                            'alias' => 'App',
                            'is_bundle' => false,
                        ],
                        'NetworkingInitCmsBundle' => [
                            'type' => 'attribute',
                            'prefix' => 'Networking\\InitCmsBundle\\Entity',
                            'dir' => '%kernel.project_dir%/vendor/networking/init-cms-bundle/src/Entity',
                        ],
                        'gedmo_translator' => [
                            'type' => 'attribute',
                            'prefix' => 'Gedmo\\Translator\\Entity',
                            'dir' => '%kernel.project_dir%/vendor/gedmo/doctrine-extensions/src/Translator/Entity',
                            'alias' => 'GedmoTranslator',
                            'is_bundle' => false,
                        ],
                        'gedmo_loggable' => [
                            'type' => 'attribute',
                            'prefix' => 'Gedmo\\Loggable\\Entity',
                            'dir' => '%kernel.project_dir%/vendor/gedmo/doctrine-extensions/src/Loggable/Entity',
                            'alias' => 'GedmoLoggable',
                            'is_bundle' => false,
                        ],
                        'gedmo_tree' => [
                            'type' => 'attribute',
                            'prefix' => 'Gedmo\\Tree\\Entity',
                            'dir' => '%kernel.project_dir%/vendor/gedmo/doctrine-extensions/src/Tree/Entity',
                            'alias' => 'GedmoTree',
                            'is_bundle' => false,
                        ],
                        'gedmo_sortable' => [
                            'type' => 'attribute',
                            'prefix' => 'Gedmo\\Sortable\\Entity',
                            'dir' => '%kernel.project_dir%/vendor/gedmo/doctrine-extensions/src/Sortable/Entity',
                            'alias' => 'GedmoSortable',
                            'is_bundle' => false,
                        ],
                    ],
                ],
            ],
        ],
    ]);
};
