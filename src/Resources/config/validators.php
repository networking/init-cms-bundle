<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Networking\InitCmsBundle\Validator\Constraints\UniqueTagValidator;
use Networking\InitCmsBundle\Validator\Constraints\UniqueURLValidator;
use Networking\InitCmsBundle\Validator\Constraints\VirusScanValidator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
            ->private();

    $services->set('networking_init_cms.unique_url_validator', UniqueURLValidator::class)
        ->tag('validator.constraint_validator', ['alias' => 'unique_url_validator']);

    $services->set('networking_init_cms.unique_tag_validator', UniqueTagValidator::class)
        ->share(false)
        ->call('setTagAdmin', [service('networking_init_cms.admin.tag')])
        ->tag('validator.constraint_validator', ['alias' => 'unique_tag_validator']);

    $services->set('networking_init_cms.clamav_validator', VirusScanValidator::class);
};
