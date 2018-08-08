<?php
/**
 * Created by PhpStorm.
 * User: yorkie
 * Date: 08.08.18
 * Time: 11:31.
 */

namespace Networking\InitCmsBundle\Provider;

use Networking\InitCmsBundle\Form\Type\MediaPreviewType;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\CDN\Server;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\VimeoProvider as BaseProvider;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class VimeoProvider extends BaseProvider
{
    /**
     * {@inheritdoc}
     */
    public function postUpdate(MediaInterface $media)
    {
        // Delete the current file from the FS
        $oldMedia = clone $media;
        $oldMedia->setProviderReference($media->getPreviousProviderReference());

        $file = $this->getReferenceFile($oldMedia);

        if ($this->getFilesystem()->has($file->getKey())) {
            $this->getFilesystem()->delete($file->getKey());
        }

        $this->postPersist($media);
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
            ['required' => false, 'label' => 'form.label_current_video', 'provider' => 'sonata.media.provider.vimeo']
        );
        $formMapper->add('binaryContent',
            TextType::class,
            ['label' => 'form.label_binary_content_vimeo_new', 'required' => false]
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
}
