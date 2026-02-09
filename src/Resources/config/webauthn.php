<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Networking\InitCmsBundle\Controller\WebAuthn\WebAuthnController;
use Networking\InitCmsBundle\Repository\WebauthnCredentialRepository;
use Networking\InitCmsBundle\Repository\WebauthnUserEntityRepository;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(WebAuthnController::class)
        ->autowire()
        ->autoconfigure()
        ->arg('$optionsStorage', service('Webauthn\\Bundle\\Security\\Storage\\SessionStorage'))
        ->tag('controller.service_arguments');

    $services->set(WebauthnCredentialRepository::class)
        ->autowire()
        ->public();

    $services->set(WebauthnUserEntityRepository::class)
        ->autowire()
        ->public();
};
