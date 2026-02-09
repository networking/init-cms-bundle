<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Networking\InitCmsBundle\Admin\GroupAdmin;
use Networking\InitCmsBundle\Admin\HelpTextAdmin;
use Networking\InitCmsBundle\Admin\LayoutBlockAdmin;
use Networking\InitCmsBundle\Admin\MenuItemAdmin;
use Networking\InitCmsBundle\Admin\PageAdmin;
use Networking\InitCmsBundle\Admin\TagAdmin;
use Networking\InitCmsBundle\Admin\TranslationAdmin;
use Sonata\DoctrineORMAdminBundle\Util\ObjectAclManipulator;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('networking_init_cms.admin.group.entity', 'Networking\\InitCmsBundle\\Entity\\Group')
        ->set('networking_init_cms.admin.group.controller', 'Sonata\\AdminBundle\\Controller\\CRUDController')
        ->set('networking_init_cms.admin.group.class', GroupAdmin::class)
        ->set('networking_init_cms.admin.translation_domain', 'SonataUserBundle')
        ->set('networking_init_cms.admin.group.translation_domain', 'SonataUserBundle');

    $services = $container->services();

    // Page Admin
    $services->set('networking_init_cms.admin.page', PageAdmin::class)
        ->public()
        ->arg(0, service('networking_init_cms.page_manager'))
        ->arg('$pageTemplates', '%networking_init_cms.page.templates%')
        ->call('setSonataAnnotationReader', [service('networking_init_cms.annotation.reader')])
        ->call('setlanguages', ['%networking_init_cms.page.languages%'])
        ->call('setTemplate', ['show', '@NetworkingInitCms/PageAdmin/page_show.html.twig'])
        ->call('setTemplate', ['preview', '@NetworkingInitCms/PageAdmin/page_preview.html.twig'])
        ->call('setTemplate', ['edit', '@NetworkingInitCms/PageAdmin/base_edit.html.twig'])
        ->call('setTemplate', ['list', '@NetworkingInitCms/PageAdmin/page_list.html.twig'])
        ->tag('sonata.admin', [
            'manager_type' => 'orm',
            'label' => 'pages',
            'model_class' => '%networking_init_cms.admin.page.class%',
            'controller' => 'Networking\\InitCmsBundle\\Controller\\PageAdminController',
            'translation_domain' => 'PageAdmin',
            'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
            'persist_filters' => true,
        ]);

    // Layout Block Admin
    $services->set('networking_init_cms.admin.layout_block', LayoutBlockAdmin::class)
        ->public()
        ->arg(0, service('networking_init_cms.page_manager'))
        ->arg(1, service('networking_init_cms.layout_block_form_listener'))
        ->arg(2, service('networking_init_cms.admin.page'))
        ->call('setSonataAnnotationReader', [service('networking_init_cms.annotation.reader')])
        ->tag('sonata.admin', [
            'manager_type' => 'orm',
            'label' => 'content',
            'show_in_roles_matrix' => false,
            'model_class' => 'Networking\\InitCmsBundle\\Entity\\LayoutBlock',
            'controller' => 'Networking\\InitCmsBundle\\Controller\\LayoutBlockController',
            'translation_domain' => 'admin',
            'persist_filters' => true,
        ]);

    // Menu Item Admin
    $services->set('networking_init_cms.admin.menu_item', MenuItemAdmin::class)
        ->public()
        ->call('setSonataAnnotationReader', [service('networking_init_cms.annotation.reader')])
        ->call('setTranslationDomain', ['MenuItemAdmin'])
        ->call('setlanguages', ['%networking_init_cms.page.languages%'])
        ->call('setSubClasses', [[
            'menu' => '%networking_init_cms.admin.menu_item.class%',
            'menu item' => '%networking_init_cms.admin.menu_item.class%',
        ]])
        ->call('setTemplate', ['list', '@NetworkingInitCms/MenuItemAdmin/menu_list.html.twig'])
        ->call('setTemplate', ['placement', '@NetworkingInitCms/MenuItemAdmin/placement.html.twig'])
        ->tag('sonata.admin', [
            'manager_type' => 'orm',
            'label' => 'menus',
            'model_class' => '%networking_init_cms.admin.menu_item.class%',
            'controller' => 'Networking\\InitCmsBundle\\Controller\\MenuItemAdminController',
            'translation_domain' => 'MenuItemAdmin',
            'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
            'persist_filters' => true,
        ]);

    // Help Text Admin
    $services->set('networking_init_cms.admin.help_text', HelpTextAdmin::class)
        ->public()
        ->call('setSonataAnnotationReader', [service('networking_init_cms.annotation.reader')])
        ->call('setTranslationDomain', ['HelpTextAdmin'])
        ->call('setlanguages', ['%networking_init_cms.page.languages%'])
        ->tag('sonata.admin', [
            'manager_type' => 'orm',
            'label' => 'help_text',
            'model_class' => 'Networking\\InitCmsBundle\\Entity\\HelpText',
            'translation_domain' => 'HelpTextAdmin',
            'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
            'persist_filters' => true,
        ]);

    // Tag Admin
    $services->set('networking_init_cms.admin.tag', TagAdmin::class)
        ->public()
        ->tag('sonata.admin', [
            'manager_type' => 'orm',
            'label' => 'tags',
            'model_class' => 'Networking\\InitCmsBundle\\Entity\\Tag',
            'controller' => 'Networking\\InitCmsBundle\\Controller\\TagAdminController',
            'translation_domain' => 'TagAdmin',
            'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
            'persist_filters' => true,
        ]);

    // Translation Admin
    $services->set('networking_init_cms.admin_entity.translation_admin', TranslationAdmin::class)
        ->arg('$defaultDomain', '%networking_init_cms.defaultDomain%')
        ->call('setEditableOptions', ['%networking_init_cms.editable%'])
        ->call('setTranslationDomain', ['TranslationAdmin'])
        ->call('setTransUnitManager', [service('lexik_translation.trans_unit.manager')])
        ->call('setManagedLocales', ['%lexik_translation.managed_locales%'])
        ->call('setDefaultSelections', ['%networking_init_cms.defaultSelections%'])
        ->call('setEmptyPrefixes', ['%networking_init_cms.emptyPrefixes%'])
        ->call('setTemplate', ['list', '@NetworkingInitCms/TranslationAdmin/list.html.twig'])
        ->tag('sonata.admin', [
            'manager_type' => 'orm',
            'label' => 'translation',
            'model_class' => 'Lexik\\Bundle\\TranslationBundle\\Entity\\TransUnit',
            'controller' => 'Networking\\InitCmsBundle\\Controller\\TranslationCRUDController',
            'translation_domain' => 'TranslationAdmin',
            'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
            'persist_filters' => true,
        ]);

    // Group Admin
    $services->set('networking_init_cms.admin.group')
        ->class('%networking_init_cms.admin.group.class%')
        ->public()
        ->call('setSonataAnnotationReader', [service('networking_init_cms.annotation.reader')])
        ->tag('sonata.admin', [
            'manager_type' => 'orm',
            'label' => 'groups',
            'model_class' => '%networking_init_cms.admin.group.entity%',
            'controller' => 'Networking\\InitCmsBundle\\Controller\\GroupAdminController',
            'translation_domain' => '%networking_init_cms.admin.group.translation_domain%',
            'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
        ]);

    $services->set('sonata.admin.manipulator.acl.object.orm', ObjectAclManipulator::class)
        ->public()
        ->autowire();
};
