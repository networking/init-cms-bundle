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
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class LayoutBlockNormalizer
    implements NormalizerInterface, DenormalizerInterface,
               CacheableSupportsMethodInterface
{

    private $managerRegistry;

    private $objectNormalizer;

    private $mediaClass = \Networking\InitCmsBundle\Entity\Media::class;

    public function __construct(
        ManagerRegistry $managerRegistry,
        ObjectNormalizer $serializer
    ) {
        $this->managerRegistry = $managerRegistry;

        $this->objectNormalizer = $serializer;
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

    /**
     * @param array $context
     */
    public function supportsNormalization(
        mixed $data,
        string $format = null /* , array $context = [] */
    ): bool
    {
        return $data instanceof LayoutBlockInterface;
    }

    public function denormalize(
        mixed $data,
        string $type,
        string $format = null,
        array $context = []
    ) {

        if($type === Media::class){

            if (isset($context[AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE])
                && $context[AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE]
            ) {
                $mediaObject = $this->managerRegistry->getManagerForClass(
                    $mediaClass
                )->find($mediaClass, $data['id']);

                $context = [
                    AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
                    AbstractObjectNormalizer::IGNORED_ATTRIBUTES => [
                        '__initializer__',
                        '__cloner__',
                        '__isInitialized__',
                    ],
                ];

                if (!$mediaObject) {
                    $mediaObject = new ($mediaClass)();
                }

                $context[AbstractObjectNormalizer::OBJECT_TO_POPULATE]  = $mediaObject;

            }

            return $this->objectNormalizer->denormalize(
                $data,
                \Networking\InitCmsBundle\Entity\Media::class,
                $format,
                $context
            );
        }


        if (isset($context[AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE])
            && $context[AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE]
        ) {
            $layoutBlock = $this->managerRegistry->getManagerForClass(
                $data['classType']
            )->find($data['classType'], $data['id']);

            $context = [
                AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
                AbstractObjectNormalizer::IGNORED_ATTRIBUTES => [
                    '__initializer__',
                    '__cloner__',
                    '__isInitialized__',
                ],
            ];

            if (!$layoutBlock) {
                $layoutBlock = new ($data['classType'])();
            }

            $context[AbstractObjectNormalizer::OBJECT_TO_POPULATE]  = $layoutBlock;

        }


        return $this->objectNormalizer->denormalize(
            $data,
            $data['classType'],
            'json',
            $context
        );

    }


    public function supportsDenormalization(
        mixed $data,
        string $type,
        string $format = null
    ) {
        return $type === LayoutBlock::class || $type === Media::class;
    }


    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}