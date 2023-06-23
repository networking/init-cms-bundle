<?php

declare(strict_types=1);

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Menu;

/**
 * Class MenuSubItemFilterIterator.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MenuSubItemFilterIterator extends \FilterIterator
{
    /**
     * @param $currentUri
     * @param string $currentUri
     */
    public function __construct(\Iterator $iterator, private $currentUri)
    {
        parent::__construct($iterator);
    }

    public function accept(): bool
    {
        if ($this->currentUri === $this->current()->getPath()
            || $this->currentUri === $this->current()->getInternalUrl()
            || $this->currentUri === $this->current()->getRedirectUrl()) {
            return true;
        }

        return false;
    }
}
