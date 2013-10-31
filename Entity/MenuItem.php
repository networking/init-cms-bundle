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

use Networking\InitCmsBundle\Model\MenuItem as ModelMenuItem;

/**
 * Class MenuItem
 * @package Networking\InitCmsBundle\Entity
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MenuItem extends ModelMenuItem
{

    /**
     * Hook on pre-persist operations
     */
    public function prePersist()
    {
        if ($this->getParent()) {
            $this->setLocale();
        }
    }
}

