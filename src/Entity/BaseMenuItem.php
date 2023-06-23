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


use Networking\InitCmsBundle\Model\MenuItem as ModelMenuItem;

class BaseMenuItem extends ModelMenuItem
{
    /**
     * Hook on pre-persist operations.
     */
    public function prePersist()
    {
        if ($this->getParent()) {
            $this->setLocale();
        }
    }
}