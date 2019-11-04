<?php
/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Doctrine;

use FOS\UserBundle\Doctrine\UserManager as FOSDoctrineUserManager;
use Networking\InitCmsBundle\Model\UserManagerInterface;

/**
 * Class UserManager.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class UserManager extends FOSDoctrineUserManager implements UserManagerInterface
{
}
