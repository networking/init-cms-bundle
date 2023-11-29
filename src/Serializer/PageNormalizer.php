<?php

namespace Networking\InitCmsBundle\Serializer;

use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PageNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public const DESERIALIZE_TRANSLATIONS = 'deserialize_translations';

    public function __construct(
        private readonly string $pageClass,
        private readonly PageManagerInterface $pageManager,
        private readonly NormalizerInterface $objectNormalizer
    ) {
    }

    public function normalize(
        mixed $object,
        string $format = null,
        array $context = []
    ) {
        $context += [
                AbstractNormalizer::CALLBACKS => [
                    'translations' => function ($innerObject, object $outerObject, string $attributeName, string $format = null, array $context = []): array {
                        return $outerObject instanceof PageInterface ? $outerObject->convertTranslationsToArray() : [];
                    },
                    'originals' => function ($innerObject, object $outerObject, string $attributeName, string $format = null, array $context = []): array {
                        return $outerObject instanceof PageInterface ? $outerObject->convertOriginalsToArray() : [];
                    },
                    'parent' => function ($innerObject, object $outerObject, string $attributeName, string $format = null, array $context = []): ?int {
                        return $outerObject instanceof PageInterface ? $outerObject->convertParentToInteger() : null;
                    },
                    'alias' => function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []): ?string {
                        return $outerObject instanceof PageInterface ? $outerObject->getAlias()?->getFullPath() : null;
                    },
                    'parents' => function ($innerObject, object $outerObject, string $attributeName, string $format = null, array $context = []): array {
                        return $outerObject instanceof PageInterface ? $outerObject->convertParentsToArray() : [];
                    },
                    'children' => function ($innerObject, object $outerObject, string $attributeName, string $format = null, array $context = []): array {
                        return $outerObject instanceof PageInterface ? $outerObject->convertChildrenToIntegerArray() : [];
                    },
                ],
                AbstractNormalizer::IGNORED_ATTRIBUTES => [
                    'allChildren',
                    'allTranslations',
                    'snapshots',
                    'snapshot',
                    'route',
                    'routes',
                    'oldTitle',
                    'tags',
                    'menuItem',
                    'original',
                    'directTranslation',
                    '__initializer__',
                    '__cloner__',
                    '__isInitialized__',
                ],
            ];

        return $this->objectNormalizer->normalize($object, $format, $context);
    }

    public function denormalize(
        mixed $data,
        string $type,
        string $format = null,
        array $context = []
    ) {
        if (is_integer($data)) {
            return $this->pageManager->find($data);
        }

        if (!array_key_exists(self::DESERIALIZE_TRANSLATIONS, $context) || $context[self::DESERIALIZE_TRANSLATIONS]) {
            $context[AbstractNormalizer::IGNORED_ATTRIBUTES][] = 'translations';
        }

        return $this->objectNormalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = [])
    {
        return $data instanceof PageInterface;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = [])
    {
        return $type === $this->pageClass;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            PageInterface::class => true,
        ];
    }
}
