<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM,
    Sonata\MediaBundle\Entity\BaseGallery as BaseGallery;

/**
 *
 * @author Yorkie Chawdick <y.chadwick@networking.ch>
 *
 * @ORM\Table(name="media__gallery")
 * @ORM\Entity()
 */
class Gallery extends BaseGallery
{

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
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