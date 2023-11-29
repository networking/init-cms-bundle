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
namespace Networking\InitCmsBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface;
use Networking\InitCmsBundle\Entity\ContentRoute;
use Networking\InitCmsBundle\Model\ContentRouteInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageSnapshotInterface;

/**
 * Class PageSnapshot.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
#[ORM\Entity]
#[ORM\Table(name: 'page_snapshot')]
class PageSnapshot implements PageSnapshotInterface
{



    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'id', type: 'integer')]
    protected $id;

    /**
     * @var Page
     */
    protected $page;

    /**
     * @var ContentRoute
     */
    #[ORM\ManyToOne(targetEntity: ContentRoute::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'content_route_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $contentRoute;


    /**
     * @var string
     */
    #[ORM\Column(name: 'resource_name', type: 'string', length: 255)]
    protected $resourceName;

    /**
     * @var int
     */
    #[ORM\Column(name: 'resource_id', type: 'integer')]
    protected $resourceId;

    /**
     * @var mixed
     */
    #[ORM\Column(name: 'versioned_data', type: 'json')]
    protected $versionedData;

    /**
     * @var int
     */
    #[ORM\Column(name: 'version', type: 'integer')]
    protected $version;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'snapshot_date', type: 'datetime')]
    protected $snapshotDate;

    /**
     * @var
     */
    #[ORM\Column(name: 'path', type: 'string', length: 255, nullable: true)]
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
     */
    public function setResourceId($resourceId): self
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
