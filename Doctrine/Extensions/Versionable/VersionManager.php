<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Doctrine\Extensions\Versionable;

use Doctrine\ORM\EntityManager;

/**
 * Class VersionManager
 * @package Networking\InitCmsBundle\Doctrine\Extensions\Versionable
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class VersionManager
{
    /**
     * @var \Doctrine\ORM\EntityManager $em
     */
    private $_em;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->_em = $em;
        $this->_em->getEventManager()->addEventSubscriber(
            new VersionListener()
        );
    }

    /**
     * @param VersionableInterface $resource
     * @return array
     */
    public function getVersions(VersionableInterface $resource)
    {
        $query = $this->_em->createQuery(
            "SELECT v FROM ResourceVersion v INDEX BY v.version ".
            "WHERE v.resourceName = ?1 AND v.resourceId = ?2 ORDER BY v.version DESC");
        $query->setParameter(1, get_class($resource));
        $query->setParameter(2, $resource->getResourceId());

        return $query->getResult();
    }
}