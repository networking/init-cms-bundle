<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Knp\Menu\MenuItem;
use Networking\InitCmsBundle\Menu\AdminMenuBuilder;
use Networking\InitCmsBundle\Menu\CmsMenuVoter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(AdminMenuBuilder::class)
        ->autowire()
        ->arg('$allowLocaleCookie', '%networking_init_cms.allow_locale_cookie%')
        ->arg('$languages', '%networking_init_cms.page.languages%');

    $services->set('AdminMenu', MenuItem::class)
        ->factory([service(AdminMenuBuilder::class), 'createAdminMenu'])
        ->tag('knp_menu.menu', ['alias' => 'AdminMenu']);

    $services->set('FrontendAdminMenu', MenuItem::class)
        ->factory([service(AdminMenuBuilder::class), 'createFrontendAdminMenu'])
        ->tag('knp_menu.menu', ['alias' => 'FrontendAdminMenu']);

    $services->set(CmsMenuVoter::class)
        ->autowire()
        ->tag('knp_menu.voter');
};
