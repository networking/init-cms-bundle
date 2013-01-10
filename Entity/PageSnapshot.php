<?php
/*
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM,
    Symfony\Cmf\Component\Routing\RouteAwareInterface,
    Networking\InitCmsBundle\Entity\Page,
    Networking\InitCmsBundle\Doctrine\Extensions\Versionable\ResourceVersionInterface,
    Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 *
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="page_snapshot")
 * @ORM\Entity(repositoryClass="Networking\InitCmsBundle\Entity\PageSnapshotRepository")
 */
class PageSnapshot implements RouteAwareInterface, ResourceVersionInterface
{

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Page $page
     *
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="snapshots", cascade={"persist"})
     */
    protected $page;

    /**
     * @var ContentRoute $contentRoute
     * @ORM\ManyToOne(targetEntity="Networking\InitCmsBundle\Entity\ContentRoute", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="content_route_id")
     */
    protected $contentRoute;

    /**
     * @ORM\Column(name="resource_name", type="string")
     */
    protected $resourceName;

    /**
     * @ORM\Column(name="resource_id", type="integer")
     */
    protected $resourceId;

    /**
     * @ORM\Column(name="versioned_data", type="array")
     */
    protected $versionedData;

    /**
     *
     * @ORM\Column(type="integer")
     */
    protected $version;

    /**
     * @ORM\Column(name="snapshot_date", type="datetime")
     */
    protected $snapshotDate;

    /**
     * @param \Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface $resource
     */
    public function __construct(VersionableInterface $resource)
    {
        $this->resourceName = get_class($resource);
        $this->resourceId = $resource->getResourceId();
        $this->version = $resource->getCurrentVersion();
        $this->snapshotDate = new \DateTime("now");
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
     * Set page
     *
     * @param  Page $page
     *
     * @return PageSnapshot
     */
    public function setPage(Page $page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get conversation
     *
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param  ContentRoute $contentRoute
     * @return PageSnapshot
     */
    public function setContentRoute(ContentRoute $contentRoute)
    {
        $contentRoute->setClassType(get_class($this));
        $contentRoute->setLocale($this->page->getLocale());
        $contentRoute->setTemplate($this->page->getTemplate());
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
     * {@inheritDoc}
     */
    public function getRoute()
    {
        return $this->contentRoute->initializeRoute($this);
    }

    /**
     * {@inheritDoc}
     */
    public function getRoutes()
    {
        return array($this->getRoute());
    }
}
