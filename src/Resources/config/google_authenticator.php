<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Networking\InitCmsBundle\GoogleAuthenticator\Helper;
use Networking\InitCmsBundle\GoogleAuthenticator\InteractiveLoginListener;
use Networking\InitCmsBundle\GoogleAuthenticator\RequestListener;
use Networking\InitCmsBundle\Helper\OneTimeCodeHelper;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('networking_init_cms.google.authenticator.helper', Helper::class)
        ->public()
        ->arg(0, '%networking_init_cms.google.authenticator.issuer%')
        ->arg(1, service('security.authorization_checker'))
        ->arg(2, '%networking_init_cms.google.authenticator.forced_for_role%')
        ->arg(3, '%networking_init_cms.google.authenticator.trusted_ip_list%');

    $services->set('networking_init_cms.google.authenticator.interactive_login_listener', InteractiveLoginListener::class)
        ->autowire(false)
        ->arg('$helper', service('networking_init_cms.google.authenticator.helper'))
        ->tag('kernel.event_listener', ['event' => 'security.interactive_login', 'method' => 'onSecurityInteractiveLogin']);

    $services->set('networking_init_cms.google.authenticator.request_listener', RequestListener::class)
        ->tag('kernel.event_listener', ['event' => 'kernel.request', 'method' => 'onCoreRequest', 'priority' => -1])
        ->arg('$helper', service('networking_init_cms.google.authenticator.helper'))
        ->arg('$tokenStorage', service('security.token_storage'))
        ->arg('$oneTimeCodeHelper', service(OneTimeCodeHelper::class))
        ->arg('$twig', service('twig'));
};
