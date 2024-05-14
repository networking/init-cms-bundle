<?php

namespace Networking\InitCmsBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Networking\InitCmsBundle\Entity\LayoutBlock;

#[AsDoctrineListener(event: Events::loadClassMetadata)]
class LayoutBlockDiscriminatorMap
{
    /**
     * @var array
     */
    protected $mapping;

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs)
    {
        $metadata = $loadClassMetadataEventArgs->getClassMetadata();
        $class = $metadata->getReflectionClass();

        if (null === $class) {
            $class = new \ReflectionClass($metadata->getName());
        }

        if (LayoutBlock::class === $class->getName()) {
            foreach ($this->mapping as $map) {
                $metadata->setDiscriminatorMap([$map['class'] => $map['class']]);
            }
        }
    }
}
