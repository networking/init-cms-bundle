<?php

declare(strict_types=1);

/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Model;

use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\ResourceVersionInterface;

/**
 * Class PageSnapshotInterface.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface PageSnapshotInterface extends RouteReferrersReadInterface, ResourceVersionInterface
{
    /**
     * @param $resourceId
     *
     * @return $this
     */
    public function setResourceId($resourceId);

    /**
     * @return int
     */
    public function getResourceId();

    /**
     * @param $resourceName
     *
     * @return $this
     */
    public function setResourceName($resourceName);

    /**
     * @return string
     */
    public function getResourceName();

    /**
     * @param $snapshotDate
     *
     * @return $this
     */
    public function setSnapshotDate($snapshotDate);

    /**
     * @return \DateTime
     */
    public function getSnapshotDate();

    /**
     * @param $version
     *
     * @return $this
     */
    public function setVersion($version);

    /**
     * @return int
     */
    public function getVersion();

    /**
     * @param $versionedData
     *
     * @return $this
     */
    public function setVersionedData($versionedData);

    /**
     * @return mixed
     */
    public function getVersionedData();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getId();

    /**
     * Set page.
     *
     * @param PageInterface $page
     *
     * @return $this
     */
    public function setPage(PageInterface $page);

    /**
     * Get conversation.
     *
     * @return PageInterface
     */
    public function getPage();

    /**
     * @param ContentRouteInterface $contentRoute
     *
     * @return $this
     */
    public function setContentRoute(ContentRouteInterface $contentRoute);

    /**
     * @return ContentRoute
     */
    public function getContentRoute();

    /**
     * @return Page
     */
    public function getOriginal();

    /**
     * @param $path
     */
    public function setPath($path);

    /**
     * @return mixed
     */
    public function getPath();
}
