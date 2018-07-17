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

use Networking\InitCmsBundle\Model\LayoutBlock as ModelLayoutBlock;

/**
 * Class LayoutBlock.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class LayoutBlock extends ModelLayoutBlock
{
    /**
     * Hook on pre-persist operations.
     */
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
