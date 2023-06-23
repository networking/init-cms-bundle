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

use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface;

/**
 * Class PageSnapshot.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class PageSnapshot implements PageSnapshotInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Page
     */
    protected $page;

    /**
     * @var ContentRoute
     */
    protected $contentRoute;

    /**
     * @var string
     */
    protected $resourceName;

    /**
     * @var int
     */
    protected $resourceId;

    /**
     * @var mixed
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

    /**
     * @var
     */
    protected $path;

    public function __construct(VersionableInterface $resource)
    {
        $this->resourceName = str_replace('Proxies\__CG__\\', '',  $resource::class);
        $this->resourceId = $resource->getResourceId();
        $this->version = $resource->getCurrentVersion();
        $this->snapshotDate = new \DateTime('now');
    }

    /**
     * @param $resourceId
     *
     * @return PageSnapshot
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;

        return $this;
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
     *
     * @return PageSnapshot
     */
    public function setResourceName($resourceName)
    {
        $this->resourceName = $resourceName;

        return $this;
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
     *
     * @return PageSnapshot
     */
    public function setSnapshotDate($snapshotDate)
    {
        $this->snapshotDate = $snapshotDate;

        return $this;
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
     *
     * @return PageSnapshot
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
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
     *
     * @return PageSnapshot
     */
    public function setVersionedData($versionedData)
    {
        $this->versionedData = $versionedData;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getVersionedData()
    {
        return $this->versionedData;
    }

    /**
     * @param int $id
     *
     * @return PageSnapshot
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set page.
     *
     * @param PageInterface $page
     *
     * @return PageSnapshot
     */
    public function setPage(PageInterface $page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get conversation.
     *
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param ContentRouteInterface $contentRoute
     *
     * @return PageSnapshot
     */
    public function setContentRoute(ContentRouteInterface $contentRoute)
    {
        $contentRoute->setClassType(static::class);
        $contentRoute->setLocale($this->page->getLocale());
        $contentRoute->setTemplateName($this->page->getTemplateName());
        $contentRoute->setName($this->page->getPageName());
        $this->contentRoute = $contentRoute;

        return $this;
    }

    /**
     * @return ContentRoute
     */
    public function getContentRoute()
    {
        return $this->contentRoute;
    }

    /**
     * @return Page
     */
    public function getOriginal()
    {
        return $this->getPage();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoute()
    {
        return $this->contentRoute->setContent($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes(): iterable
    {
        return [$this->getRoute()];
    }

    /**
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }
}
