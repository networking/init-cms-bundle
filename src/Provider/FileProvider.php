<?php
/**
 * Created by PhpStorm.
 * User: yorkie
 * Date: 06.08.18
 * Time: 13:11.
 */

namespace Networking\InitCmsBundle\Provider;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\CDN\Server;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\FileProvider as BaseProvider;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class FileProvider extends BaseProvider
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
        $formMapper->add('binaryContent',
            FileType::class,
            ['label' => 'form.label_binary_content_new', 'required' => false]
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
