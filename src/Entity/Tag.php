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

use Networking\InitCmsBundle\Model\Tag as ModelTag;

/**
 * Class Tag.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class Tag extends ModelTag
{
    /**
     * @var int
     */
    protected $id;
}
