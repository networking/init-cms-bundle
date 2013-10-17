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

use Networking\InitCmsBundle\Model\Text as ModelText;


/**
 * Networking\InitCmsBundle\Model
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 *
 * @author net working AG <info@networking.ch>
 */
abstract class BaseText extends ModelText
{

    /**
     * PrePersist method
     */
    public function prePersist()
    {

        $this->createdAt = $this->updatedAt = new \DateTime();
    }

    /**
     * PostPersist method
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }

}
