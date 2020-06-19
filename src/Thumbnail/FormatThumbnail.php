<?php
/**
 * Created by PhpStorm.
 * User: yorkie
 * Date: 06.08.18
 * Time: 11:28.
 */

namespace Networking\InitCmsBundle\Thumbnail;

use Networking\InitCmsBundle\Provider\ImageProvider;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Resizer\ResizerInterface;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;

class FormatThumbnail implements ThumbnailInterface
{
    /**
     * @var string
     */
    private $defaultFormat;

    /**
     * @var ResizerInterface[]
     */
    private $resizers = [];

    /**
     * @param string $defaultFormat
     */
    public function __construct($defaultFormat)
    {
        $this->defaultFormat = $defaultFormat;
    }

    /**
     * @param string $id
     */
    public function addResizer($id, ResizerInterface $resizer)
    {
        if (!isset($this->resizers[$id])) {
            $this->resizers[$id] = $resizer;
        }
    }

    /**
     * @param string $id
     *
     * @return ResizerInterface
     * @throws \Exception
     *
     */
    public function getResizer($id)
    {
        if (!isset($this->resizers[$id])) {
            throw new \LogicException(sprintf('Resizer with id: "%s" is not attached.', $id));
        }

        return $this->resizers[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        if (MediaProviderInterface::FORMAT_REFERENCE === $format || in_array($media->getContentType(),ImageProvider::SVG_CONTENT_TYPES)) {
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
        if (MediaProviderInterface::FORMAT_REFERENCE === $format || in_array($media->getContentType(),ImageProvider::SVG_CONTENT_TYPES)) {
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
    public function generate(MediaProviderInterface $provider, MediaInterface $media)
    {
        if (!$provider->requireThumbnails()) {
            return;
        }

        $referenceFile = $provider->getReferenceFile($media);

        if (!$referenceFile->exists()) {
            return;
        }

        if(in_array($media->getContentType(),ImageProvider::SVG_CONTENT_TYPES)){
            return;
        }
        foreach ($provider->getFormats() as $format => $settings) {

            if (substr($format, 0, \strlen($media->getContext())) === $media->getContext() ||
                MediaProviderInterface::FORMAT_ADMIN === $format) {
                $resizer = (isset($settings['resizer']) && ($settings['resizer'])) ?
                    $this->getResizer($settings['resizer']) :
                    $provider->getResizer();

                $resizer->resize(
                    $media,
                    $referenceFile,
                    $provider->getFilesystem()->get($provider->generatePrivateUrl($media, $format), true),
                    $this->getExtension($media),
                    $settings
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(MediaProviderInterface $provider, MediaInterface $media, $formats = null)
    {
        if (null === $formats) {
            $formats = array_keys($provider->getFormats());
        } elseif (\is_string($formats)) {
            $formats = [$formats];
        }

        if (!\is_array($formats)) {
            throw new \InvalidArgumentException('"Formats" argument should be string or array');
        }

        foreach ($formats as $format) {
            $path = $provider->generatePrivateUrl($media, $format);
            if ($path && $provider->getFilesystem()->has($path)) {
                $provider->getFilesystem()->delete($path);
            }
        }
    }

    /**
     * @return string the file extension for the $media, or the $defaultExtension if not available
     */
    protected function getExtension(MediaInterface $media)
    {
        $ext = $media->getExtension();
        if (!\is_string($ext) || \strlen($ext) < 3) {
            $ext = $this->defaultFormat;
        }

        return $ext;
    }
}
