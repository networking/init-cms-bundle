<?php


namespace Networking\InitCmsBundle\EventListener;


use Doctrine\Common\Annotations\AnnotationReader;
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

        if ($class === null) {
            $class = new \ReflectionClass($metadata->getName());
        }


        if ($class->getName() == LayoutBlock::class) {
            foreach ($this->mapping as $map) {
                $reader = new AnnotationReader();
                $discriminatorMap = array();

                if (null !== $discriminatorMapAnnotation = $reader->getClassAnnotation(
                        $class,
                        'Doctrine\ORM\Mapping\DiscriminatorMap'
                    )) {
                    $discriminatorMap = $discriminatorMapAnnotation->value;
                }
                $discriminatorMap = array_merge($discriminatorMap, array($map['name'] => $map['class']));

                $metadata->setDiscriminatorMap($discriminatorMap);
            }
        }
    }
}