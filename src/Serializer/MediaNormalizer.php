<?php

namespace Networking\InitCmsBundle\Serializer;

use Sonata\MediaBundle\Model\Media;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

readonly class MediaNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        private NormalizerInterface $objectNormalizer,
        private NormalizerInterface $propertyNormalizer,
        private string $mediaClass = \Networking\InitCmsBundle\Entity\Media::class,
    ) {
    }

    public function normalize(
        mixed $data,
        ?string $format = null,
        array $context = [],
    ): array|string|int|float|bool|\ArrayObject|null {
        if ($data instanceof $this->mediaClass) {
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

            return $this->objectNormalizer->normalize($data, $format, $context);
        }

        return $this->propertyNormalizer->normalize($data, $format, $context);
    }

    public function denormalize(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): mixed {
        return $this->propertyNormalizer->denormalize($data, $this->mediaClass, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return Media::class === $type || $type === $this->mediaClass;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
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
