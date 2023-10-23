<?php

namespace Networking\InitCmsBundle\Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Networking\InitCmsBundle\Entity\LayoutBlock;
use Networking\InitCmsBundle\Model\IgnoreRevertInterface;
use Networking\InitCmsBundle\Model\LayoutBlockInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sonata\MediaBundle\Model\Media;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class LayoutBlockNormalizer implements NormalizerInterface, DenormalizerInterface
{


    public function __construct(
        private readonly NormalizerInterface $objectNormalizer,
        private readonly NormalizerInterface $propertyNormalizer,
    ) {
    }

    public function normalize(
        mixed $object,
        string $format = null,
        array $context = []
    ) {

        if (!$object instanceof LayoutBlockInterface) {
            throw new \InvalidArgumentException(
                'The object must implement the LayoutBlockInterface'
            );
        }

        return $this->objectNormalizer->normalize($object, $format, $context);
    }

    public function denormalize(
        mixed $data,
        string $type,
        string $format = null,
        array $context = []
    ) {

        return $this->propertyNormalizer->denormalize(
            $data,
            $data['classType'],
            'json',
            $context
        );

    }


    public function supportsNormalization(mixed $data,string $format = null /* , array $context = [] */): bool
    {
        return $data instanceof LayoutBlockInterface;
    }


    public function supportsDenormalization(mixed $data,string $type,string $format = null): bool {
        return $type === LayoutBlock::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            LayoutBlock::class => true,
        ];
    }
}