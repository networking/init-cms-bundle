<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Networking\InitCmsBundle\DataFixtures\LoadHelpText;
use Networking\InitCmsBundle\DataFixtures\LoadLayoutBlocks;
use Networking\InitCmsBundle\DataFixtures\LoadMenu;
use Networking\InitCmsBundle\DataFixtures\LoadPages;
use Networking\InitCmsBundle\Entity\ContentRouteListener;
use Networking\InitCmsBundle\Entity\ContentRouteManager;
use Networking\InitCmsBundle\Entity\EntityChangedListener;
use Networking\InitCmsBundle\Entity\HelpTextManager;
use Networking\InitCmsBundle\Entity\LastEditedListener;
use Networking\InitCmsBundle\Entity\LayoutBlockFormListener;
use Networking\InitCmsBundle\Entity\MenuItemManager;
use Networking\InitCmsBundle\Entity\PageListener;
use Networking\InitCmsBundle\Entity\PageManager;
use Networking\InitCmsBundle\Entity\PageSnapshotManager;
use Networking\InitCmsBundle\Entity\UserManager;
use Networking\InitCmsBundle\EventListener\LayoutBlockDiscriminatorMap;
use Networking\InitCmsBundle\EventListener\UserActivityListener;
use Networking\InitCmsBundle\Form\Type\MediaEntityType;
use Networking\InitCmsBundle\Helper\LanguageSwitcherHelper;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('DEFAULT_TRACKING', false);

    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
            ->private();

    // Helpers
    $services->set(LanguageSwitcherHelper::class)
        ->arg('$fallbackRoute', '%networking_init_cms.translation_fallback_route%');

    // Object Managers
    $services->set('networking_init_cms.page_manager', PageManager::class)
        ->lazy()
        ->arg('$class', '%networking_init_cms.admin.page.class%');

    $services->set('networking_init_cms.user_manager', UserManager::class)
        ->arg('$class', '%networking_init_cms.admin.user.class%')
        ->arg('$userPasswordHasher', service('security.password_hasher'))
        ->arg('$canonicalFieldsUpdater', service('sonata.user.util.canonical_fields_updater'));

    $services->set('networking_init_cms.page_snapshot_manager', PageSnapshotManager::class)
        ->arg('$class', 'Networking\\InitCmsBundle\\Entity\\PageSnapshot');

    $services->set('networking_init_cms.menu_item_manager', MenuItemManager::class)
        ->arg('$class', '%networking_init_cms.admin.menu_item.class%');

    $services->set('networking_init_cms.help_text_manager', HelpTextManager::class)
        ->arg('$class', 'Networking\\InitCmsBundle\\Entity\\HelpText');

    $services->set('networking_init_cms.content_route_manager', ContentRouteManager::class)
        ->arg('$class', 'Networking\\InitCmsBundle\\Entity\\ContentRoute');

    // Event Listeners
    $services->set('networking_init_cms.event_listener.user_activity', UserActivityListener::class)
        ->call('setTokenStorage', [service('security.token_storage')])
        ->tag('kernel.event_listener', ['event' => 'kernel.controller', 'method' => 'onCoreController']);

    $services->set('networking_init_cms.layout_block_form_listener', LayoutBlockFormListener::class)
        ->lazy()
        ->arg('$contentTypes', '%networking_init_cms.page.content_types%');

    $services->set(LayoutBlockDiscriminatorMap::class)
        ->arg('$mapping', '%networking_init_cms.page.content_types%');

    $services->set(EntityChangedListener::class)
        ->lazy()
        ->arg(0, service('networking_init_cms.logger'))
        ->arg('$loggingActive', '%env(default:DEFAULT_TRACKING:bool:NETWORKING_TRACK_USER)%');

    $services->set(ContentRouteListener::class)
        ->lazy()
        ->arg('$templates', '%networking_init_cms.page.templates%');

    $services->set(LastEditedListener::class)
        ->lazy();

    $services->set(PageListener::class)
        ->lazy();

    // Form Types
    $services->set(MediaEntityType::class)
        ->tag('form.type', ['alias' => 'media_entity_type']);

    $services->set(LoadHelpText::class)
        ->arg('$languages', '%networking_init_cms.page.languages%')
        ->tag('doctrine.fixture.orm');

    $services->set(LoadLayoutBlocks::class)
        ->arg('$languages', '%networking_init_cms.page.languages%')
        ->arg('$contentTypes', '%networking_init_cms.page.content_types%')
        ->arg('$templates', '%networking_init_cms.page.templates%')
        ->arg('$pageClass', '%networking_init_cms.admin.page.class%')
        ->tag('doctrine.fixture.orm');

    $services->set(LoadMenu::class)
        ->arg('$languages', '%networking_init_cms.page.languages%')
        ->arg('$pageClass', '%networking_init_cms.admin.page.class%')
        ->tag('doctrine.fixture.orm');

    $services->set(LoadPages::class)
        ->arg('$languages', '%networking_init_cms.page.languages%')
        ->arg('$templates', '%networking_init_cms.page.templates%')
        ->arg('$pageClass', '%networking_init_cms.admin.page.class%')
        ->tag('doctrine.fixture.orm');
};
