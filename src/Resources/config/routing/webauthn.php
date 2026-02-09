<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import('.', 'webauthn');

    $routes->import('@NetworkingInitCmsBundle/Controller/WebAuthn/WebAuthnController.php', 'attribute');
};
