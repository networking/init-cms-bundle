<?php

namespace Networking\InitCmsBundle\Document;

use Networking\InitCmsBundle\Model\GalleryView as ModelGalleryView;
/**
 * Networking\GalleryBundle\Entity\GalleryView
 */
class GalleryView extends ModelGalleryView
{

    /**
     */
    public function onPrePersist()
    {

        $this->createdAt = $this->updatedAt = new \DateTime("now");
    }

    /**
     * Hook on pre-update operations
     */
    public function onPreUpdate()
    {
        $this->updatedAt = new \DateTime('now');
    }

}

