<?php

declare(strict_types=1);

use Networking\InitCmsBundle\Action\MaintenanceAction;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import('@NetworkingInitCmsBundle/Resources/config/routing/cms.php')
        ->prefix('/');

    $routes->import('@SonataAdminBundle/Resources/config/routing/sonata_admin.php')
        ->prefix('/admin');

    $routes->import('.', 'sonata_admin')
        ->prefix('/admin');

    $routes->import('@SonataUserBundle/Resources/config/routing/admin_security.php')
        ->prefix('/admin');

    $routes->import('@SonataUserBundle/Resources/config/routing/admin_resetting.php')
        ->prefix('/admin');

    $routes->import('@SonataMediaBundle/Resources/config/routing/media.php')
        ->prefix('/media');

    $routes->import('.', 'uploader');

    $routes->add('networking_init_cms_maintenance', '/maintenance.html')
        ->controller(MaintenanceAction::class);

    if ('dev' === ($routes->env() ?? '')) {
        $routes->add('networking_init_cms_maintenance_debug', '/_debug/maintenance.html')
            ->controller(MaintenanceAction::class);
    }
};
