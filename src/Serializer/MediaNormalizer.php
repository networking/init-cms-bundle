<?php

namespace Networking\InitCmsBundle\Serializer;

use Sonata\MediaBundle\Model\Media;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MediaNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        private readonly NormalizerInterface $objectNormalizer,
        private readonly NormalizerInterface $propertyNormalizer,
        private readonly string $mediaClass = \Networking\InitCmsBundle\Entity\Media::class
    ) {
    }

    public function normalize(
        mixed $object,
        string $format = null,
        array $context = []
    ): array|string|int|float|bool|\ArrayObject|null {
        if ($object instanceof $this->mediaClass) {
            $context[AbstractNormalizer::IGNORED_ATTRIBUTES] = [
                'lazyObjectState',
                'lazyObjectInitialized',
                'lazyObjectAsInitialized',
                'statusList',
                'metadataValue',
                'statusList',
                '__initializer__',
                '__cloner__',
                '__isInitialized__', ];

            return $this->objectNormalizer->normalize($object, $format, $context);
        }

        return $this->propertyNormalizer->normalize($object, $format, $context);
    }

    public function denormalize(
        mixed $data,
        string $type,
        string $format = null,
        array $context = []
    ): mixed {
        return $this->propertyNormalizer->denormalize($data, $this->mediaClass, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return Media::class === $type || $type === $this->mediaClass;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof Media;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            $this->mediaClass => true,
        ];
    }
}
