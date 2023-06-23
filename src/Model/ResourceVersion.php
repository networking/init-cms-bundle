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
namespace Networking\InitCmsBundle\Model;

use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\ResourceVersionInterface;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface;

/**
 * Class ResourceVersion.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class ResourceVersion implements ResourceVersionInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $resourceName;

    /**
     * @var int
     */
    protected $resourceId;

    /**
     * @var
     */
    protected $versionedData;

    /**
     * @var int
     */
    protected $version;

    /**
     * @var \DateTime
     */
    protected $snapshotDate;

    public function __construct(VersionableInterface $resource)
    {
        $this->resourceName = $resource::class;
        $this->resourceId = $resource->getResourceId();
        $this->versionedData = $resource->getVersionedData();
        $this->version = $resource->getCurrentVersion();
        $this->snapshotDate = new \DateTime('now');
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $resourceId
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;
    }

    /**
     * @return int
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * @param $resourceName
     */
    public function setResourceName($resourceName)
    {
        $this->resourceName = $resourceName;
    }

    /**
     * @return string
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }

    /**
     * @param $snapshotDate
     */
    public function setSnapshotDate($snapshotDate)
    {
        $this->snapshotDate = $snapshotDate;
    }

    /**
     * @return \DateTime
     */
    public function getSnapshotDate()
    {
        return $this->snapshotDate;
    }

    /**
     * @param $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param $versionedData
     */
    public function setVersionedData($versionedData)
    {
        $this->versionedData = $versionedData;
    }

    /**
     * @return array
     */
    public function getVersionedData()
    {
        return $this->versionedData;
    }
}
