<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Networking\InitCmsBundle\Block\CacheBlockService;
use Networking\InitCmsBundle\Block\OnlineUsersBlockService;
use Networking\InitCmsBundle\Block\PagesBlockService;
use Networking\InitCmsBundle\Block\SitemapBlockService;
use Networking\InitCmsBundle\Block\TranslatableTextBlockService;
use Networking\InitCmsBundle\Block\VersionBlockService;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
            ->public();

    $services->set('networking_init_cms.block.service.translatable_text', TranslatableTextBlockService::class)
        ->tag('sonata.block');

    $services->set('networking_init_cms.block.service.online_users', OnlineUsersBlockService::class)
        ->tag('sonata.block');

    $services->set('networking_init_cms.block.service.pages', PagesBlockService::class)
        ->tag('sonata.block');

    $services->set('networking_init_cms.block.service.cache', CacheBlockService::class)
        ->tag('sonata.block');

    $services->set('networking_init_cms.block.service.sitemap', SitemapBlockService::class)
        ->tag('sonata.block');

    $services->set('networking_init_cms.block.service.version', VersionBlockService::class)
        ->arg('$projectDir', '%kernel.project_dir%')
        ->tag('sonata.block');
};
