<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Networking\InitCmsBundle\Form\Extension\ModelTypeExtension;
use Networking\InitCmsBundle\Form\Extension\Select2Extension;
use Networking\InitCmsBundle\Form\Type\IconradioType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
            ->private();

    $services->load('Networking\\InitCmsBundle\\Form\\', '../../Form')
        ->exclude('../../Form/{DataTransformer,ChoiceList}')
        ->public();

    $services->set(ModelTypeExtension::class)
        ->tag('form.type_extension', ['extended-type' => ModelType::class]);

    $services->set(Select2Extension::class)
        ->tag('form.type_extension', ['extended-type' => ChoiceType::class]);

    $services->set(IconradioType::class)
        ->arg('$templates', '%networking_init_cms.page.templates%');
};
