<?php

namespace Networking\InitCmsBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Lexik\Bundle\TranslationBundle\Entity\TransUnit;
use Networking\InitCmsBundle\Repository\TransUnitRepository;

#[AsDoctrineListener( event: Events::loadClassMetadata)]
readonly class TransUnitEventListener
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /**
         * @var \Doctrine\ORM\Mapping\ClassMetadata $classMetadata
         */
        $classMetadata = $eventArgs->getClassMetadata();

        if($classMetadata->getName() === TransUnit::class) {
            $classMetadata->customRepositoryClassName = TransUnitRepository::class;
        }
    }
}