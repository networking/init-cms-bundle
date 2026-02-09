<?php

namespace Networking\InitCmsBundle\Serializer;

use Networking\InitCmsBundle\Entity\LayoutBlock;
use Networking\InitCmsBundle\Model\LayoutBlockInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LayoutBlockNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        private readonly NormalizerInterface $objectNormalizer,
        private readonly NormalizerInterface $propertyNormalizer,
    ) {
    }

    public function normalize(
        mixed $data,
        ?string $format = null,
        array $context = [],
    ): array|string|int|float|bool|\ArrayObject|null {
        if (!$data instanceof LayoutBlockInterface) {
            throw new \InvalidArgumentException('The object must implement the LayoutBlockInterface');
        }

        return $this->objectNormalizer->normalize($data, $format, $context);
    }

    public function denormalize(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): mixed {
        return $this->propertyNormalizer->denormalize(
            $data,
            $data['classType'],
            'json',
            $context
        );
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof LayoutBlockInterface;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return LayoutBlock::class === $type;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            LayoutBlock::class => true,
        ];
    }
}
