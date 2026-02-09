<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Networking\InitCmsBundle\Component\EventDispatcher\CmsEventDispatcher;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionListener;
use Networking\InitCmsBundle\EventListener\AdminContentSecurityPolicyListener;
use Networking\InitCmsBundle\EventListener\AdminTrackerListener;
use Networking\InitCmsBundle\EventListener\BodyListener;
use Networking\InitCmsBundle\EventListener\CacheCleaner;
use Networking\InitCmsBundle\EventListener\LocaleListener;
use Networking\InitCmsBundle\EventListener\MaintenanceListener;
use Networking\InitCmsBundle\EventListener\TransUnitEventListener;
use Networking\InitCmsBundle\EventSubscriber\LoginSubscriber;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
            ->private();

    $services->set(CmsEventDispatcher::class);

    $services->set(BodyListener::class)
        ->tag('kernel.event_listener', ['event' => 'kernel.request', 'method' => 'onKernelRequest', 'priority' => 0]);

    $services->set(AdminContentSecurityPolicyListener::class)
        ->tag('kernel.event_listener', ['event' => 'kernel.response', 'method' => 'onKernelResponse', 'priority' => -255]);

    $services->set(VersionListener::class)
        ->tag('doctrine.event_listener', ['event' => 'postUpdate', 'method' => 'postUpdate'])
        ->tag('doctrine.event_listener', ['event' => 'postPersist', 'method' => 'postPersist']);

    $services->set(AdminTrackerListener::class)
        ->tag('kernel.event_listener', ['event' => 'kernel.request', 'method' => 'onKernelRequest', 'priority' => 0]);

    $services->set(MaintenanceListener::class)
        ->arg('$projectDir', '%kernel.project_dir%')
        ->tag('kernel.event_listener', ['event' => 'kernel.request', 'method' => 'onKernelRequest', 'priority' => 500]);

    $services->set(LocaleListener::class)
        ->arg('$firewallMap', service('security.firewall.map'))
        ->arg('$availableLanguages', '%networking_init_cms.page.languages%')
        ->arg('$defaultLocale', '%kernel.default_locale%')
        ->arg('$allowLocaleCookie', '%networking_init_cms.allow_locale_cookie%')
        ->arg('$singleLanguage', '%networking_init_cms.single_language%')
        ->tag('kernel.event_listener', ['event' => 'kernel.request', 'method' => 'onKernelRequest', 'priority' => 33])
        ->tag('kernel.event_listener', ['event' => 'security.interactive_login', 'method' => 'onSecurityInteractiveLogin', 'priority' => 10]);

    $services->set(CacheCleaner::class);

    $services->set(TransUnitEventListener::class);

    $services->set(LoginSubscriber::class)
        ->tag('kernel.event_subscriber', ['dispatcher' => 'security.event_dispatcher.admin']);
};
