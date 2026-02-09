<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('_configure_cms', '/cms_install')
        ->controller('Networking\InitCmsBundle\Controller\InstallController::indexAction');

    $routes->add('_welcome_cms', '/welcome')
        ->controller('Networking\InitCmsBundle\Controller\InstallController::indexAction');

    $routes->add('_install_db', '/install_db/{complete}')
        ->controller('Networking\InitCmsBundle\Controller\InstallController::installDbAction')
        ->defaults(['complete' => 0])
        ->requirements(['complete' => '\d+']);
};
