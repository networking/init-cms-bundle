<?php

/*
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Component\Menu;

class MenuItemFilterIterator extends \FilterIterator
{
    /**
     * @var
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

        if ($this->currentUri === $this->current()->getPath()) {
            return true;
        }

        return false;

    }
}
