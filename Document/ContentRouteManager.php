<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Document;

use Doctrine\ODM\MongoDB\DocumentManager;
use Networking\InitCmsBundle\Doctrine\ContentRouteManager as DoctrineContentRouteManager;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class ContentRouteManager
 * @package Networking\InitCmsBundle\Document
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class ContentRouteManager extends DoctrineContentRouteManager
{

    public function __construct(DocumentManager $dm, $class, Session $session)
    {
        parent::__construct($dm, $class, $session);
    }
}
