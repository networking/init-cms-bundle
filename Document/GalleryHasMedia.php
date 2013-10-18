<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Document;

use Sonata\MediaBundle\Entity\BaseGalleryHasMedia;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 *
 */
class GalleryHasMedia extends BaseGalleryHasMedia
{

    /**
     * @var integer $id
     *
     *
     */
    protected $id;

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }
}