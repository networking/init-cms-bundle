<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Networking\InitCmsBundle\Admin\Extension\GalleryAdminExtension;
use Networking\InitCmsBundle\Admin\MediaAdmin;
use Sonata\MediaBundle\Admin\GalleryItemAdmin;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('sonata.media.admin.media.class', MediaAdmin::class)
        ->set('sonata.media.admin.media.controller', 'Networking\\InitCmsBundle\\Controller\\MediaAdminController')
        ->set('sonata.media.admin.media.translation_domain', 'MediaAdmin');

    $services = $container->services();

    $services->set('gallery_admin_extension', GalleryAdminExtension::class)
        ->arg('$pool', service('sonata.media.pool'))
        ->tag('sonata.admin.extension', ['target' => 'sonata.media.admin.gallery']);

    $services->set('sonata.media.admin.media')
        ->class('%sonata.media.admin.media.class%')
        ->public()
        ->arg('$pool', service('sonata.media.pool'))
        ->arg('$validator', service('validator'))
        ->call('setModelManager', [service('sonata.media.admin.media.manager')])
        ->call('setLanguages', ['%networking_init_cms.page.languages%'])
        ->call('setMultipleMediaTags', ['%networking_init_cms.multiple_media_tags%'])
        ->call('setShowTagTree', ['%networking_init_cms.show_tag_tree%'])
        ->call('setTemplates', [[
            'list' => '@NetworkingInitCms/MediaAdmin/list.html.twig',
            'edit' => '@NetworkingInitCms/MediaAdmin/edit.html.twig',
            'create' => '@NetworkingInitCms/MediaAdmin/multifileupload_jquery.html.twig',
            'show' => '@NetworkingInitCms/CRUD/show.html.twig',
            'ajax' => '@NetworkingInitCms/ajax_layout.html.twig',
        ]])
        ->tag('sonata.admin', [
            'manager_type' => 'orm',
            'label' => 'admin_label_media',
            'model_class' => '%sonata.media.media.class%',
            'controller' => '%sonata.media.admin.media.controller%',
            'translation_domain' => '%sonata.media.admin.media.translation_domain%',
            'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
            'persist_filters' => true,
        ]);

    $services->set('sonata.media.admin.gallery_item', GalleryItemAdmin::class)
        ->tag('sonata.admin', [
            'model_class' => '%sonata.media.gallery_item.class%',
            'controller' => '%sonata.admin.configuration.default_controller%',
            'translation_domain' => '%sonata.media.admin.media.translation_domain%',
            'manager_type' => 'orm',
            'show_in_roles_matrix' => false,
            'show_in_dashboard' => false,
            'group' => 'sonata_media',
            'label' => 'gallery_item',
            'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
        ]);
};
