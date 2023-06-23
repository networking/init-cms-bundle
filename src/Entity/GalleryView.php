<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Networking\InitCmsBundle\Entity;

use Networking\InitCmsBundle\Model\GalleryView as ModelGalleryView;

/**
 * Class GalleryView.
 */
class GalleryView extends ModelGalleryView
{
    public function prePersist()
    {
        $this->createdAt = $this->updatedAt = new \DateTime('now');
    }

    /**
     * Hook on pre-update operations.
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now');
    }
}
