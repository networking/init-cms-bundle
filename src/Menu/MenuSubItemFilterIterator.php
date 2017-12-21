<?php
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
 * Class MenuSubItemFilterIterator
 * @package Networking\InitCmsBundle\Component\Menu
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MenuSubItemFilterIterator extends \FilterIterator
{
    /**
     * @var string
     */
    private $currentUri;

    /**
     * @param \Iterator $iterator
     * @param $currentUri
     */
    public function __construct(\Iterator $iterator, $currentUri)
    {
        $this->currentUri = $currentUri;
        parent::__construct($iterator);
    }

    /**
     * @return bool
     */
    public function accept()
    {

        if ($this->currentUri === $this->current()->getPath()
            || $this->currentUri === $this->current()->getInternalUrl()
            || $this->currentUri === $this->current()->getRedirectUrl()) {
            return true;
        }

        return false;

    }
}
