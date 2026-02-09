<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Networking\InitCmsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface;
use Networking\InitCmsBundle\Model\ContentRouteInterface;
use Networking\InitCmsBundle\Model\MenuItemInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\MappedSuperclass()]
class BaseMenuItem implements MenuItemInterface, \IteratorAggregate, \Stringable
{
    protected array $options = [];

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', length: 255)]
    protected string $name;

    protected ?PageInterface $page = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $redirectUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $internalUrl = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    protected ?bool $hidden = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $linkTarget = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $linkClass = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $linkRel = null;

    #[ORM\Column(name: 'lft', type: 'integer')]
    #[Gedmo\TreeLeft]
    protected int $lft;

    #[ORM\Column(name: 'lvl', type: 'integer')]
    #[Gedmo\TreeLevel]
    protected int $lvl;

    #[ORM\Column(name: 'rgt', type: 'integer')]
    #[Gedmo\TreeRight]
    protected int $rgt;

    #[ORM\Column(name: 'root', type: 'integer', nullable: true)]
    #[Gedmo\TreeRoot]
    protected ?int $root = null;

    protected ?MenuItemInterface $parent = null;

    protected Collection $children;

    #[ORM\Column(type: 'boolean')]
    protected bool $isRoot = false;

    #[ORM\Column(type: 'string', length: 6)]
    protected string $locale;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $description = null;

    #[ORM\Column(type: 'string', length: 50)]
    protected string $visibility = self::VISIBILITY_PUBLIC;

    protected ?string $path = null;

    protected bool $wasValidated = false;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        if ($this->getParent()) {
            $this->setLocale();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return $this
     */
    public function setPage(?PageInterface $page = null): static
    {
        $this->page = $page;

        return $this;
    }

    public function getPage(): ?PageInterface
    {
        return $this->page;
    }

    /**
     * @return $this
     */
    public function setRedirectUrl(?string $redirectUrl = null): static
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    /**
     * @return $this
     */
    public function setHidden(?bool $hidden = null): static
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getHidden(): ?bool
    {
        return $this->hidden;
    }

    public function isHidden(): bool
    {
        return $this->getHidden() ?? false;
    }

    public function setInternalUrl(?string $route = null): static
    {
        $this->internalUrl = $route;

        return $this;
    }

    public function getInternalUrl(): ?string
    {
        return $this->internalUrl;
    }

    public function setName(?string $name = null): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setParent(?MenuItemInterface $parent = null): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent(): ?MenuItemInterface
    {
        return $this->parent;
    }

    public function setLft($lft): static
    {
        $this->lft = $lft;

        return $this;
    }

    public function getLft(): int
    {
        return $this->lft;
    }

    public function setLvl($lvl): static
    {
        $this->lvl = $lvl;

        return $this;
    }

    public function getLvl(): int
    {
        return $this->lvl;
    }

    public function setRgt($rgt): static
    {
        $this->rgt = $rgt;

        return $this;
    }

    public function getRgt(): int
    {
        return $this->rgt;
    }

    public function setRoot(int $root): static
    {

        $this->root = $root;

        return $this;
    }

    public function getRoot(): int
    {
        return $this->root;
    }

    public function getMenu(): MenuItemInterface
    {
        return $this->getRootParent($this);
    }

    public function setIsRoot(bool $isRoot): static
    {
        $this->isRoot = $isRoot;

        return $this;
    }

    public function getIsRoot(): bool
    {
        return $this->isRoot;
    }

    public function isRoot(): bool
    {
        return $this->isRoot;
    }

    public function getRootParent(MenuItemInterface $menuItem): MenuItemInterface
    {
        if ($parent = $menuItem->getParent()) {
            return $this->getRootParent($parent);
        }

        return $menuItem;
    }

    public function getParentByLevel($level = 1): ?MenuItemInterface
    {
        if ($level === $this->getLvl()) {
            return $this;
        }

        if (!$this->getParent()) {
            return null;
        }

        if ($level === $this->getParent()->getLvl()) {
            return $this->getParent();
        }

        return $this->getParent()->getParentByLevel($level);
    }

    public function setMenu(?MenuItemInterface $menuItem = null): static
    {
        if ($menuItem) {
            $this->setParent($menuItem);
        }

        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getActiveChildren(): Collection
    {
        $children = new ArrayCollection();
        foreach ($this->getChildren() as $child) {
            if (!$child->getPage()->isActive()) {
                continue;
            }
            $children->add($child);
        }

        return $children;
    }

    public function getChildrenByStatus($status): Collection
    {
        $children = new ArrayCollection();
        foreach ($this->getChildren() as $child) {
            if (VersionableInterface::STATUS_PUBLISHED === $status) {
                if ($child->getPage() && !$child->getPage()->getSnapshot()) {
                    continue;
                }
            }
            $children->add($child);
        }

        return $children;
    }

    public function setPath($path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getPath(): ?string
    {
        if ($this->path) {
            return $this->path;
        }

        if (!$this->getPage()) {
            return null;
        }

        return $this->getPage()->getContentRoute()->getPath();
    }

    /**
     * @return bool|int|string
     */
    public function getRouteId(): ?int
    {
        if (!$this->getPage()) {
            return null;
        }

        return $this->getPage()->getContentRoute()->getId();
    }

    /**
     * @return bool|ContentRouteInterface
     */
    public function getContentRoute()
    {
        if (!$this->getPage()) {
            return false;
        }

        return $this->getPage()->getContentRoute();
    }

    public function setLocale(?string $locale = null): static
    {
        if (is_null($locale)) {
            $locale = $this->getParent()->getLocale();
        }
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setDescription(?string $description = null): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getIterator(): \Traversable
    {
        return $this->children->getIterator();
    }

    public function setLinkClass(?string $linkClass = null): static
    {
        $this->linkClass = $linkClass;

        return $this;
    }

    public function getLinkClass(): ?string
    {
        return $this->linkClass;
    }

    public function setLinkRel(?string $linkRel = null): static
    {
        $this->linkRel = $linkRel;

        return $this;
    }

    public function getLinkRel(): ?string
    {
        return $this->linkRel;
    }

    public function setLinkTarget(?string $linkTarget = null): static
    {
        $this->linkTarget = $linkTarget;

        return $this;
    }

    public function getLinkTarget(): ?string
    {
        return $this->linkTarget;
    }

    public function setVisibility(string $visibility): static
    {
        if (!in_array($visibility, [self::VISIBILITY_PROTECTED, self::VISIBILITY_PUBLIC])) {
            throw new \InvalidArgumentException('Invalid visibility');
        }
        $this->visibility = $visibility;

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public static function getVisibilityList(): array
    {
        return [
            'visibility_public' => self::VISIBILITY_PUBLIC,
            'visibility_protected' => self::VISIBILITY_PROTECTED,
        ];
    }

    public function getLinkAttributes(): array
    {
        $linkAttributes = [];

        if (!empty($this->linkTarget)) {
            $linkAttributes['target'] = $this->linkTarget;
        }
        if (!empty($this->linkClass)) {
            $linkAttributes['class'] = $this->linkClass;
        }
        if (!empty($this->linkRel)) {
            $linkAttributes['rel'] = $this->linkRel;
        }

        return $linkAttributes;
    }

    public function hasChildren(): bool
    {
        return $this->children->count() > 0;
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


    public function hasPage(): bool
    {
        return !is_null($this->page);
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if ($this->wasValidated) {
            return;
        }
        if ($this->getIsRoot()) {
            return;
        }
        if (!$this->getRedirectUrl() && !$this->getPage() && !$this->getInternalUrl()) {
            $context->buildViolation('menu.page_or_url.required')
                ->atPath('page')
                ->addViolation();
        }
        $this->wasValidated = true;
    }
}
