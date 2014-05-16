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

use Doctrine\ORM\EntityManager;
use Networking\InitCmsBundle\Doctrine\ContentRouteManager as DoctrineContentRouteManager;
use Symfony\Component\HttpFoundation\Session\Session;
/**
 * @author net working AG <info@networking.ch>
 */
class ContentRouteManager extends DoctrineContentRouteManager
{
    public function __construct(EntityManager $em, $class)
    {
        parent::__construct($em, $class);
    }



}
