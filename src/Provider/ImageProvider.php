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

class ImageProvider extends BaseProvider
{
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
        $formMapper->add(
            'self',
            MediaPreviewType::class,
            ['required' => false, 'label' => 'form.label_image', 'provider' => 'sonata.media.provider.image']
        );
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
		if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
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
}
