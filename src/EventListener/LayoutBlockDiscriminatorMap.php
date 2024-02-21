<?php

namespace Networking\InitCmsBundle\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Networking\InitCmsBundle\Entity\LayoutBlock;

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

    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        $metadata = $event->getClassMetadata();
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
