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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface;

/**
 * Class Tag.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
#[ORM\Entity]
#[ORM\Table(name: 'tag')]
#[ORM\UniqueConstraint(name: 'path_idx', columns: ['path'])]
#[Gedmo\Tree(type: 'materializedPath')]
class Tag
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255)]
    #[Gedmo\TreePathSource()]
    protected $name;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Gedmo\TreePath(separator: '/', appendId: true)]
    protected $path;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: true, name: 'lvl')]
    #[Gedmo\TreeLevel()]
    protected $level;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255)]
    #[Gedmo\Slug(fields: ['name'], updatable: true, separator: '-', unique: false)]
    protected $slug;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(
        targetEntity: 'Networking\InitCmsBundle\Entity\Tag',
        mappedBy: 'parent',
        fetch: 'LAZY',
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['path' => 'ASC'])]
    protected $children = [];

    #[ORM\ManyToOne(
        targetEntity: 'Networking\InitCmsBundle\Entity\Tag',
        inversedBy: 'children',
    )]
    #[ORM\JoinColumn(
        name: 'parent_id',
        referencedColumnName: 'id',
        onDelete: 'SET NULL'
    )]
    #[Gedmo\TreeParent()]
    protected $parent;

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

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function hasChild(): bool
    {
        return !$this->children->isEmpty();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }
}
