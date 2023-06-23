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
namespace Networking\InitCmsBundle\Doctrine\Extensions\Versionable;

use Doctrine\ORM\EntityManager;

/**
 * Class VersionManager.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class VersionManager
{

    /**
     * @return array
     */
    public function getVersions(VersionableInterface $resource)
    {
        $query = $this->_em->createQuery(
            'SELECT v FROM ResourceVersion v INDEX BY v.version '.
            'WHERE v.resourceName = ?1 AND v.resourceId = ?2 ORDER BY v.version DESC');
        $query->setParameter(1, $resource::class);
        $query->setParameter(2, $resource->getResourceId());

        return $query->getResult();
    }
}
