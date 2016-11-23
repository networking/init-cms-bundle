<?php
namespace Networking\InitCmsBundle\Provider;

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\YouTubeProvider as BaseProvider;

/**
 * This file is part of the init-cms-sandbox  package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class YouTubeProvider extends BaseProvider
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
    protected function fixBinaryContent(MediaInterface $media)
    {
        if (!$media->getBinaryContent()) {
            return;
        }
        if (strlen($media->getBinaryContent()) === 11) {
            return;
        }
        if (preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\#\?&\"'>]+)/", $media->getBinaryContent(), $matches)) {
            $media->setBinaryContent($matches[1]);
        }
    }
}