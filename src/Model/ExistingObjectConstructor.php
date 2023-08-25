<?php

namespace Networking\InitCmsBundle\Model;

use Doctrine\Persistence\ManagerRegistry;
use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;

class ExistingObjectConstructor implements ObjectConstructorInterface
{
    public const ATTRIBUTE = 'deserialization-constructor-target';

    private $fallbackConstructor;
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry, ObjectConstructorInterface $fallbackConstructor)
    {
        $this->managerRegistry = $managerRegistry;
        $this->fallbackConstructor = $fallbackConstructor;
    }

    /**
     * @inheritDoc
     */
    public function construct(
        DeserializationVisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context
    ): ?object {
        if ($context->hasAttribute(self::ATTRIBUTE)) {

            return $context->getAttribute(self::ATTRIBUTE);

            $objectManager = $this->managerRegistry->getManagerForClass($metadata->name);

            $objectManager->initializeObject($object);

            return $object;

        }

        return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
    }

    public function constructFromObject(
        DeserializationVisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context,
        $object
    ): ?object {


        return $object;
    }
}