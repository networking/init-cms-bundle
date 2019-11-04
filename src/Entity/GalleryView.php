<?php

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
