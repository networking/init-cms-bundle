<?php
/**
 * Created by PhpStorm.
 * User: yorkie
 * Date: 06.08.18
 * Time: 13:11.
 */

namespace Networking\InitCmsBundle\Provider;

use Networking\InitCmsBundle\Form\Type\MediaPreviewType;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\CDN\Server;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\ImageProvider as BaseProvider;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageProvider extends BaseProvider
{
    /** @var string */
    const SVG_CONTENT_TYPES = ['image/svg', 'image/svg+xml'];

    /**
     * @param MediaInterface $media
     *
     * @return string
     */
    protected function generateReferenceName(MediaInterface $media)
    {
        return $media->getMetadataValue('filename');
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper)
    {
        $formMapper->add('name');
        $formMapper->add('authorName');
        $formMapper->add('description');
        $formMapper->add('copyright');
        $formMapper->add('binaryContent',
            FileType::class,
            ['label' => 'form.label_binary_content_image_new', 'required' => false]
        );
        $formMapper->add('enabled', null, ['required' => false], ['inline_block' => true]);
        if (!$this->getCdn() instanceof Server) {
            $formMapper->add(
                'cdnIsFlushable',
                null,
                ['required' => false],
                ['inline_block' => true]
            );
        }
    }



	/**
	 * {@inheritdoc}
	 */
	public function generatePublicUrl(MediaInterface $media, $format)
	{
		if (MediaProviderInterface::FORMAT_REFERENCE === $format || in_array($media->getContentType(), self::SVG_CONTENT_TYPES)) {
			$path = $this->getReferenceImage($media);
		} else {
			$path = $this->thumbnail->generatePublicUrl($this, $media, $format);
		}

		$path = str_replace(' ', '%20', $path);

        // if $path is already an url, no further action is required
        if (null !== parse_url($path, PHP_URL_SCHEME)) {
	        return $path;
        }

        return $this->getCdn()->getPath($path, $media->getCdnIsFlushable());
    }

    protected function doTransform(MediaInterface $media)
    {
        $this->fixBinaryContent($media);
        $this->fixFilename($media);

        if ($media->getBinaryContent() instanceof UploadedFile && 0 === $media->getBinaryContent()->getSize()) {
            $media->setProviderReference(uniqid($media->getName(), true));
            $media->setProviderStatus(MediaInterface::STATUS_ERROR);

            throw new UploadException('The uploaded file is not found');
        }

        // this is the name used to store the file
        if (!$media->getProviderReference() ||
            MediaInterface::MISSING_BINARY_REFERENCE === $media->getProviderReference()
        ) {
            $media->setProviderReference($this->generateReferenceName($media));
        }

        if ($media->getBinaryContent() instanceof File) {
            $media->setContentType($media->getBinaryContent()->getMimeType());
            $media->setSize($media->getBinaryContent()->getSize());
        }
        $media->setProviderStatus(MediaInterface::STATUS_OK);
        if(in_array($media->getContentType(), self::SVG_CONTENT_TYPES)){
            $media->setWidth(200);
            $media->setHeight(200);

            return;
        }

        if ([] === $this->allowedExtensions) {
            $media->setProviderStatus(MediaInterface::STATUS_ERROR);

            throw new UploadException('There are no allowed extensions for this image.');
        }

        if ([] === $this->allowedMimeTypes) {
            $media->setProviderStatus(MediaInterface::STATUS_ERROR);

            throw new UploadException('There are no allowed mime types for this image.');
        }

        $binaryContent = $media->getBinaryContent();

        if ($binaryContent instanceof UploadedFile) {
            $extension = $binaryContent->getClientOriginalExtension();
        } elseif ($binaryContent instanceof File) {
            $extension = $binaryContent->getExtension();
        } else {
            // Should not happen, FileProvider should throw an exception in that case
            return;
        }

        $extension = '' !== $extension ? $extension : $binaryContent->guessExtension();

        if (!\in_array(strtolower($extension), $this->allowedExtensions, true)) {
            $media->setProviderStatus(MediaInterface::STATUS_ERROR);

            throw new UploadException(sprintf(
                'The image extension "%s" is not one of the allowed (%s).',
                $extension,
                '"'.implode('", "', $this->allowedExtensions).'"'
            ));
        }

        $mimeType = $binaryContent->getMimeType();

        if (!\in_array($mimeType, $this->allowedMimeTypes, true)) {
            $media->setProviderStatus(MediaInterface::STATUS_ERROR);

            throw new UploadException(sprintf(
                'The image mime type "%s" is not one of the allowed (%s).',
                $mimeType,
                '"'.implode('", "', $this->allowedMimeTypes).'"'
            ));
        }

        try {
            $image = $this->imagineAdapter->open($binaryContent->getPathname());
        } catch (\RuntimeException $e) {
            $media->setProviderStatus(MediaInterface::STATUS_ERROR);

            return;
        }

        $size = $image->getSize();

        $media->setWidth($size->getWidth());
        $media->setHeight($size->getHeight());

        $media->setProviderStatus(MediaInterface::STATUS_OK);
    }
}
