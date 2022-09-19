<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Generator;

use Sonata\MediaBundle\Generator\GeneratorInterface;
use Sonata\MediaBundle\Model\MediaInterface;

class MediaPathGenerator implements GeneratorInterface
{
    /**
     * @param MediaInterface $media
     *
     * @return string
     */
    public function generatePath(MediaInterface $media): string
    {
        return sprintf('%s/%s', $media->getContext(), $media->getId());
    }
}
