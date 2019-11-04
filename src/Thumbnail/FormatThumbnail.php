<?php
/**
 * Created by PhpStorm.
 * User: yorkie
 * Date: 06.08.18
 * Time: 11:28.
 */

namespace Networking\InitCmsBundle\Thumbnail;

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail as BaseFormatThumbnail;

class FormatThumbnail extends BaseFormatThumbnail implements ThumbnailInterface
{
    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
            return $provider->getReferenceImage($media);
        }

        return sprintf(
            '%s/thumb_%s/%s',
            $provider->generatePath($media),
            $format,
            $media->getMetadataValue('filename')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function generatePrivateUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
            return $provider->getReferenceImage($media);
        }

        return sprintf(
            '%s/thumb_%s/%s',
            $provider->generatePath($media),
            $format,
            $media->getMetadataValue('filename')

        );
    }
}
