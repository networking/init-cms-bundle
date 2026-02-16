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

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

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
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Gedmo\TreePathSource]
    protected string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Gedmo\TreePath(separator: '/', appendId: false)]
    protected ?string $path = null;

    #[ORM\Column(name: 'lvl', type: 'integer', nullable: true)]
    #[Gedmo\TreeLevel]
    protected int $level;

    #[ORM\Column(type: 'string', length: 255)]
    #[Gedmo\Slug(fields: ['name'], updatable: true, unique: false, separator: '-')]
    protected string $slug;

    #[ORM\OneToMany(
        targetEntity: 'Networking\InitCmsBundle\Entity\Tag',
        mappedBy: 'parent',
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['path' => 'ASC'])]
    protected Collection|array $children = [];

    #[ORM\ManyToOne(
        targetEntity: 'Networking\InitCmsBundle\Entity\Tag',
        inversedBy: 'children',
    )]
    #[ORM\JoinColumn(
        name: 'parent_id',
        referencedColumnName: 'id',
        onDelete: 'SET NULL'
    )]
    #[Gedmo\TreeParent]
    protected ?Tag $parent = null;

    protected array $parentNames;

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(?string $name = null): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setSlug(?string $slug = null): false|Tag|static
    {
        if (!empty($this->slug)) {
            return false;
        }
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path = null): void
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

    public function setParent(?Tag $parent = null): void
    {
        $this->parent = $parent;
    }

    public function getParent(): ?Tag
    {
        return $this->parent;
    }

    public function getChildren(): Collection|array
    {
        if (!$this->children) {
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
