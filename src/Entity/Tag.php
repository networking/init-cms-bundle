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
    #[Gedmo\TreePath(separator: '/', appendId: false)]
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

    /**
     * @var array
     */
    protected $parentNames;

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
     * Set name.
     *
     * @param string $name
     *
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }


    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return Tag|false
     */
    public function setSlug($slug)
    {
        if (!empty($this->slug)) {
            return false;
        }
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
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

    public function setParent(Tag $parent = null)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getChildren(): Collection
    {
        if(!$this->children){
            return [];
        }
        return $this->children;
    }

    public function getAdminTitle(): ?string
    {
        return $this->path;
    }

    public function setParentNames(array $parentNames): self
    {
        $this->parentNames = $parentNames;

        return $this;
    }

    public function getParentNames(): array
    {
        if (!$this->parentNames) {
            $page = $this;
            $parentNames = [$page->getName()];

            while ($page->getParent()) {
                $page = $page->getParent();
                $parentNames[] = $page->getName();
            }

            $this->setParentNames(array_reverse($parentNames));
        }

        return $this->parentNames;
    }

    public function hasChild($id): bool
    {
        foreach ($this->getChildren() as $child) {
            if ($child->getId() == $id) {
                return true;
            }

            return $child->hasChild($id);
        }

        return false;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
