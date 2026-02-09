<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Gedmo\Blameable\BlameableListener;
use Gedmo\IpTraceable\IpTraceableListener;
use Gedmo\Loggable\LoggableListener;
use Gedmo\Mapping\Driver\AttributeReader;
use Gedmo\Sluggable\SluggableListener;
use Gedmo\SoftDeleteable\SoftDeleteableListener;
use Gedmo\Sortable\SortableListener;
use Gedmo\Timestampable\TimestampableListener;
use Gedmo\Translatable\TranslatableListener;
use Gedmo\Tree\TreeListener;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('gedmo.attribute_reader', AttributeReader::class);

    // Doctrine Extension listeners to handle behaviors
    $services->set('gedmo.listener.tree', TreeListener::class)
        ->tag('doctrine.event_listener', ['event' => 'prePersist'])
        ->tag('doctrine.event_listener', ['event' => 'preUpdate'])
        ->tag('doctrine.event_listener', ['event' => 'preRemove'])
        ->tag('doctrine.event_listener', ['event' => 'onFlush'])
        ->tag('doctrine.event_listener', ['event' => 'loadClassMetadata'])
        ->tag('doctrine.event_listener', ['event' => 'postPersist'])
        ->tag('doctrine.event_listener', ['event' => 'postUpdate'])
        ->tag('doctrine.event_listener', ['event' => 'postRemove'])
        ->call('setAnnotationReader', [service('gedmo.attribute_reader')]);

    $services->set(TranslatableListener::class)
        ->tag('doctrine.event_listener', ['event' => 'postLoad'])
        ->tag('doctrine.event_listener', ['event' => 'postPersist'])
        ->tag('doctrine.event_listener', ['event' => 'preFlush'])
        ->tag('doctrine.event_listener', ['event' => 'onFlush'])
        ->tag('doctrine.event_listener', ['event' => 'loadClassMetadata'])
        ->call('setAnnotationReader', [service('gedmo.attribute_reader')])
        ->call('setDefaultLocale', ['%env(LOCALE)%'])
        ->call('setTranslationFallback', [false]);

    $services->set('gedmo.listener.timestampable', TimestampableListener::class)
        ->tag('doctrine.event_listener', ['event' => 'prePersist'])
        ->tag('doctrine.event_listener', ['event' => 'onFlush'])
        ->tag('doctrine.event_listener', ['event' => 'loadClassMetadata'])
        ->call('setAnnotationReader', [service('gedmo.attribute_reader')]);

    $services->set('gedmo.listener.sluggable', SluggableListener::class)
        ->tag('doctrine.event_listener', ['event' => 'prePersist'])
        ->tag('doctrine.event_listener', ['event' => 'onFlush'])
        ->tag('doctrine.event_listener', ['event' => 'loadClassMetadata'])
        ->call('setAnnotationReader', [service('gedmo.attribute_reader')]);

    $services->set('gedmo.listener.sortable', SortableListener::class)
        ->tag('doctrine.event_listener', ['event' => 'onFlush'])
        ->tag('doctrine.event_listener', ['event' => 'loadClassMetadata'])
        ->tag('doctrine.event_listener', ['event' => 'prePersist'])
        ->tag('doctrine.event_listener', ['event' => 'postPersist'])
        ->tag('doctrine.event_listener', ['event' => 'preUpdate'])
        ->tag('doctrine.event_listener', ['event' => 'postRemove'])
        ->tag('doctrine.event_listener', ['event' => 'postFlush'])
        ->call('setAnnotationReader', [service('gedmo.attribute_reader')]);

    $services->set('gedmo.listener.softdeleteable', SoftDeleteableListener::class)
        ->tag('doctrine.event_listener', ['event' => 'onFlush'])
        ->tag('doctrine.event_listener', ['event' => 'loadClassMetadata'])
        ->call('setAnnotationReader', [service('gedmo.attribute_reader')]);

    $services->set(LoggableListener::class)
        ->tag('doctrine.event_listener', ['event' => 'onFlush'])
        ->tag('doctrine.event_listener', ['event' => 'loadClassMetadata'])
        ->tag('doctrine.event_listener', ['event' => 'postPersist'])
        ->call('setAnnotationReader', [service('gedmo.attribute_reader')]);

    $services->set(BlameableListener::class)
        ->tag('doctrine.event_listener', ['event' => 'prePersist'])
        ->tag('doctrine.event_listener', ['event' => 'onFlush'])
        ->tag('doctrine.event_listener', ['event' => 'loadClassMetadata'])
        ->call('setAnnotationReader', [service('gedmo.attribute_reader')]);

    $services->set(IpTraceableListener::class)
        ->tag('doctrine.event_listener', ['event' => 'prePersist'])
        ->tag('doctrine.event_listener', ['event' => 'onFlush'])
        ->tag('doctrine.event_listener', ['event' => 'loadClassMetadata'])
        ->call('setAnnotationReader', [service('gedmo.attribute_reader')]);
};
