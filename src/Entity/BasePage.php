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
use Gedmo\Sluggable\Util\Urlizer;
use Networking\InitCmsBundle\Model\ContentRouteInterface;
use Networking\InitCmsBundle\Model\LayoutBlockInterface;
use Networking\InitCmsBundle\Model\MenuItemInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageSnapshotInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * Networking\InitCmsBundle\Entity\BasePage.
 *
 * @author net working AG <info@networking.ch>
 */
#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable]
#[Gedmo\Tree(type: 'materializedPath')]
abstract class BasePage implements PageInterface
{
    /**
     * @var int|string|null
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    protected $id;

    #[ORM\Column(type: 'datetime')]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Versioned]
    protected ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'string', length: 255)]
    protected ?string $pageName = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $metaTitle = null;

    #[Gedmo\TreePathSource()]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $url = null;

    #[Gedmo\TreePath(separator: '/')]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $path = null;

    #[Gedmo\TreeLevel()]
    #[ORM\Column(type: 'integer', name: 'lvl', nullable: true)]
    protected ?int $level = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $metaKeyword = null;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $metaDescription = null;

    protected PageInterface|int|null $parent = null;

    /**
     * @var array<int, PageInterface>
     */
    #[Ignore]
    protected array $parents = [];
    /**
     * @var Collection<int, PageInterface>
     */
    #[Ignore]
    protected Collection|array $children;

    protected array $allChildren = [];

    #[Ignore]
    protected ?PageInterface $alias = null;

    protected ?Collection $layoutBlocks = null;

    /**
     * @var Collection<int, MenuItemInterface>
     */
    #[Ignore]
    protected ?Collection $menuItem = null;

    #[ORM\Column(type: 'boolean')]
    protected bool $isHome = false;

    #[ORM\Column(type: 'string', length: 50)]
    protected string $status = self::STATUS_DRAFT;

    #[ORM\Column(type: 'string', length: 50)]
    protected string $visibility = self::VISIBILITY_PUBLIC;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $activeFrom = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $activeTo = null;

    #[ORM\Column(type: 'string', length: 6)]
    protected ?string $locale = null;

    /**
     * @var Collection<int, PageInterface>
     */
    protected Collection|array $translations;

    /**
     * @var Collection<int, PageInterface>
     */
    protected Collection|array $originals;

    #[Ignore]
    protected ?ContentRouteInterface $contentRoute = null;

    /**
     * @var Collection<int, PageSnapshotInterface>
     */
    #[Ignore]
    protected ?Collection $snapshots = null;

    #[Ignore]
    protected string $snapshotClassType = PageSnapshot::class;

    protected ?MediaInterface $socialMediaImage = null;

    protected ?string $aliasFullPath = null;

    #[Ignore]
    protected ?string $oldTitle = null;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->originals = new ArrayCollection();
        $this->layoutBlocks = new ArrayCollection();
        $this->menuItem = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    #[ORM\PrePersist()]
    public function prePersist(): void
    {
        $this->createdAt = $this->updatedAt = new \DateTime('now');

        if (!$this->metaTitle) {
            $this->setMetaTitle($this->pageName);
        }
    }

    #[ORM\PreUpdate()]
    public function preUpdate(): void
    {
        $this->setUpdatedAt();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(): self
    {
        $this->updatedAt = new \DateTime('now');

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getTitle(): ?string
    {
        return $this->pageName;
    }

    public function setPageName(?string $title): self
    {
        $this->oldTitle = $this->pageName;
        $this->pageName = $title;

        if ($this->pageName && $this->getContentRoute()) {
            $this->getContentRoute()->setName($this->pageName);
        }

        return $this;
    }

    public function getPageName(): ?string
    {
        return $this->pageName;
    }

    public function setMetaTitle(?string $title): self
    {
        $this->metaTitle = $title;

        return $this;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setMetaKeyword(?string $metaKeyword): void
    {
        $this->metaKeyword = $metaKeyword;
    }

    public function getMetaKeyword(): ?string
    {
        return $this->metaKeyword;
    }

    public function setMetaDescription(?string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setParent(PageInterface $parent = null): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return PageInterface|null
     */
    public function getParent(int $level = -1): PageInterface|int|null
    {
        if (-1 === $level) {
            return $this->parent;
        }

        $parents = $this->getParents();

        if ($level < 0) {
            $level = count($parents) + $level;
        }

        return isset($parents[$level]) ? $parents[$level] : null;
    }

    public function setAlias(?PageInterface $alias)
    {
        $this->alias = $alias;
    }

    public function getAlias(): ?PageInterface
    {
        return $this->alias;
    }

    /**
     * @param array<int, PageInterface> $parents
     *
     * @return $this
     */
    public function setParents(array $parents): self
    {
        $this->parents = $parents;

        return $this;
    }

    /**
     * @return array<int, PageInterface>
     */
    public function getParents(): array
    {
        if (!$this->parents) {
            $page = $this;
            $parents = [];

            while ($page->getParent()) {
                $page = $page->getParent();
                $parents[] = $page;
            }

            $this->setParents(array_reverse($parents));
        }

        return $this->parents;
    }

    /**
     * @return $this
     */
    public function addChildren(PageInterface $children): self
    {
        $this->children[] = $children;

        $children->setParent($this);

        return $this;
    }

    public function getChildren(): Collection|array
    {
        return $this->children;
    }

    public function setChildren(array $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function getAllChildren(array &$children = []): array
    {
        if (!$this->allChildren && $this->getChildren()) {
            $page = $this;
            foreach ($page->getChildren() as $child) {
                $children[] = $child;
                if ($child->getChildren()) {
                    $child->getAllChildren($children);
                }
            }

            $this->setAllChildren(array_reverse($children));
        }

        return $this->allChildren;
    }

    public function setAllChildren(array $children): self
    {
        $this->allChildren = $children;

        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function setStatus(?string $status): self
    {
        if (!in_array(
            $status,
            [
                self::STATUS_DRAFT,
                self::STATUS_PUBLISHED,
                self::STATUS_REVIEW,
                self::STATUS_OFFLINE,
            ]
        )
        ) {
            throw new \InvalidArgumentException('Invalid status');
        }
        $this->status = $status;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status ?? self::STATUS_DRAFT;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function setVisibility(string $visibility): self
    {
        if (!in_array(
            $visibility,
            [self::VISIBILITY_PROTECTED, self::VISIBILITY_PUBLIC]
        )
        ) {
            throw new \InvalidArgumentException('Invalid visibility');
        }
        $this->visibility = $visibility;

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function isDraft(): bool
    {
        return self::STATUS_DRAFT === $this->status;
    }

    public function isReview(): bool
    {
        return self::STATUS_REVIEW === $this->status;
    }

    public function isPublished(): bool
    {
        return self::STATUS_PUBLISHED === $this->status;
    }

    public function isOffline(): bool
    {
        return self::STATUS_OFFLINE == $this->status;
    }

    public function isActive(): bool
    {
        $now = new \DateTime();

        if ($now->getTimestamp() >= $this->getActiveStart()->getTimestamp()
            && $now->getTimestamp() <= $this->getActiveEnd()->getTimestamp()
        ) {
            return self::STATUS_PUBLISHED == $this->status;
        }

        return false;
    }

    public function getActiveStart(): \DateTimeInterface
    {
        if (!$this->activeFrom) {
            return new \DateTime();
        }

        return $this->activeFrom;
    }

    /**
     * @param \Datetime $activeTo
     */
    public function setActiveTo(?\DateTimeInterface $activeTo): self
    {
        $this->activeTo = $activeTo;

        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getActiveTo(): ?\DateTimeInterface
    {
        return $this->activeTo;
    }

    public function setActiveFrom(?\DateTimeInterface $activeFrom): self
    {
        $this->activeFrom = $activeFrom;

        return $this;
    }

    public function getActiveFrom(): ?\DateTimeInterface
    {
        return $this->activeFrom;
    }

    public function getActiveEnd(): ?\DateTimeInterface
    {
        if (!$this->activeTo) {
            return new \DateTime();
        }

        return $this->activeTo;
    }

    public function addLayoutBlock(LayoutBlockInterface $layoutBlock): self
    {
        $layoutBlock->setPage($this);
        $this->layoutBlocks->add($layoutBlock);

        return $this;
    }

    public function removeLayoutBlock(LayoutBlockInterface $layoutBlock): self
    {
        $this->layoutBlocks->removeElement($layoutBlock);

        return $this;
    }

    public function setLayoutBlocks($layoutBlocks): self
    {
        if ('array' == gettype($layoutBlocks)) {
            $layoutBlocks = new ArrayCollection($layoutBlocks);
        }

        foreach ($layoutBlocks as $block) {
            $block->setPage($this);
        }

        $this->layoutBlocks = $layoutBlocks;

        return $this;
    }

    public function orderLayoutBlocks(): void
    {
        $layoutBlocks = $this->layoutBlocks->toArray();

        usort($layoutBlocks, [__CLASS__, 'compareSortOrder']);
        $this->layoutBlocks = new ArrayCollection();
        foreach ($layoutBlocks as $layoutBlock) {
            $this->layoutBlocks->add($layoutBlock);
        }
    }

    private function compareSortOrder(
        LayoutBlockInterface $a,
        LayoutBlockInterface $b
    ): int {
        return $a->getSortOrder() - $b->getSortOrder();
    }

    public function getLayoutBlocks(string $zone = null, $includingInactive = false): Collection|array
    {
        if (!is_null($zone)) {
            return $this->layoutBlocks->filter(
                function (?LayoutBlockInterface $layoutBlock) use ($zone, $includingInactive) {
                    if (is_null($layoutBlock)) {
                        return false;
                    }

                    return $layoutBlock->getZone() == $zone && ($includingInactive || $layoutBlock->isActive());
                }
            );
        }

        return $this->layoutBlocks;
    }

    public function setMenuItem(MenuItemInterface $menuItem): self
    {
        $menuItem->setPage($this);
        $this->menuItem->add($menuItem);

        return $this;
    }

    public function removeMenuItem(MenuItemInterface $menuItem): self
    {
        $this->menuItem->removeElement($menuItem);

        return $this;
    }

    public function getMenuItem(): Collection
    {
        return $this->menuItem;
    }

    public function getMenuItemByRoot($rootId): Collection
    {
        return $this->menuItem->filter(
            function ($menuItem) use ($rootId) {
                return $menuItem->getRoot() == $rootId;
            }
        );
    }

    public function __toString(): string
    {
        if ($this->pageName) {
            return $this->pageName;
        }
        if ($this->oldTitle) {
            return $this->oldTitle;
        }

        return '-------';
    }

    public function setIsHome(bool $isHome): self
    {
        $this->isHome = $isHome;

        return $this;
    }

    public function getIsHome(): bool
    {
        return $this->isHome;
    }

    public function isHome(): bool
    {
        return $this->isHome;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        $this->getContentRoute()->setLocale($locale);

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setOriginals(array $originals): self
    {
        if ('array' == gettype($originals)) {
            $originals = new ArrayCollection($originals);
        }

        $this->originals = $originals;

        return $this;
    }

    public function setOriginal(?PageInterface $page): self
    {
        if (!$page) {
            return $this;
        }
        $this->originals->add($page);

        return $this;
    }

    public function getOriginals(): Collection|array
    {
        return $this->originals;
    }

    #[Ignore]
    public function getOriginal(): ?PageInterface
    {
        return $this->originals->first() ?: null;
    }

    public function isDirectTranslation(PageInterface $page): bool
    {
        if ($this->originals->contains($page)) {
            return true;
        }

        if ($this->translations->contains($page)) {
            return true;
        }

        return false;
    }

    public function getDirectTranslation(): ?PageInterface
    {
        foreach ($this->getAllTranslations() as $translation) {
            if ($translation->isDirectTranslation($this)) {
                return $translation;
            }
        }

        return null;
    }

    public function getDirectTranslationFor(PageInterface $page): ?PageInterface
    {
        foreach ($this->getAllTranslations() as $translation) {
            if ($translation->isDirectTranslation($page)) {
                return $translation;
            }
        }

        return null;
    }

    public function addTranslation(PageInterface $page): self
    {
        $this->translations->add($page);
        $page->getOriginals()->add($this);

        return $this;
    }

    public function removeTranslation(PageInterface $page): self
    {
        $this->translations->removeElement($page);
        $page->getOriginals()->removeElement($this);

        return $this;
    }

    public function setTranslations(array|Collection $translations): self
    {
        if ('array' == gettype($translations)) {
            $translations = new ArrayCollection($translations);
        }

        $this->translations = $translations;

        return $this;
    }

    /**
     * @return Collection<int, PageInterface>
     */
    public function getTranslations(): Collection|array
    {
        return $this->translations;
    }

    public function getTranslatedLocales(): array
    {
        $locales = [];

        if ($this->getAllTranslations()->count()) {
            foreach ($this->getAllTranslations() as $translation) {
                $locales[] = $translation->getLocale();
            }
        }

        return $locales;
    }

    public function getAdminTitle(): string
    {
        $countParents = count($this->getParents());
        $prefix = '';
        for ($i = 0; $i < $countParents; ++$i) {
            $prefix .= '- ';
        }

        return $prefix.''.$this->getPageName();
    }

    public function setUrl(?string $url)
    {
        if (!$url) {
            return;
        }
        $url = Urlizer::urlize($url);
        $this->url = $url;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setContentRoute(ContentRouteInterface $contentRoute): self
    {
        $this->contentRoute = $contentRoute;

        return $this;
    }

    /**
     * @return $this
     */
    public function setSnapshots($snapshots): self
    {
        if ('array' == gettype($snapshots)) {
            $snapshots = new ArrayCollection($snapshots);
        } elseif (!$snapshots instanceof ArrayCollection) {
            $snapshots = new ArrayCollection([$snapshots]);
        }

        foreach ($snapshots as $snapshot) {
            $snapshot->setPage($this);
        }

        $this->snapshots = $snapshots;

        return $this;
    }

    /**
     * @return Collection<int, PageSnapshotInterface>
     */
    public function getSnapshots(): Collection
    {
        if (!$this->snapshots) {
            return new ArrayCollection();
        }

        return $this->snapshots;
    }

    public function getRoute(): RouteObjectInterface
    {
        return ContentRouteManager::generateRoute(
            $this->contentRoute,
            $this->contentRoute->getPath(),
            $this
        );
    }

    public function getRoutes(): iterable
    {
        return [$this->getRoute()];
    }

    public function setTemplate(string $template): self
    {
        if (!$this->id) {
            $this->getContentRoute()->setClassType(get_class($this));
            $this->getContentRoute()->setLocale($this->getLocale());
        }
        $this->getContentRoute()->setTemplate($template);

        return $this;
    }

    public function getTemplate(): ?string
    {
        if (!$this->contentRoute) {
            return null;
        }

        return $this->contentRoute->getTemplate();
    }

    public function setTemplateName(string $templateName): self
    {
        if (!$this->id) {
            $this->getContentRoute()->setClassType(get_class($this));
            $this->getContentRoute()->setLocale($this->getLocale());
        }
        $this->getContentRoute()->setTemplateName($templateName);

        return $this;
    }

    public function getTemplateName(): ?string
    {
        return $this->getContentRoute()?->getTemplateName();
    }

    public function getFullPath(): ?string
    {
        return $this->getContentRoute()?->getPath();
    }

    public function setAliasFullPath(?string $aliasFullPath): self
    {
        $this->aliasFullPath = $aliasFullPath;

        return $this;
    }

    public function getAliasFullPath(): ?string
    {
        if ($this->aliasFullPath) {
            return $this->aliasFullPath;
        }

        if (!$this->getAlias()) {
            return null;
        }

        return $this->alias->getFullPath();
    }

    public function getAllTranslations(): Collection
    {
        $translationsArray = [];

        $this->buildAllTranslations($translationsArray);

        return new ArrayCollection($translationsArray);
    }

    public function buildAllTranslations(array &$translationsArray): void
    {
        // find all possible translations
        if ($this->getTranslations() && !$this->getTranslations()->isEmpty()) {
            foreach ($this->getTranslations() as $translation) {
                if (array_key_exists($translation->getLocale(), $translationsArray)) {
                    return;
                }
                // if we already meet you stop and go on with the next
                $translationsArray[$translation->getLocale()] = $translation;
                $translation->buildAllTranslations($translationsArray);
            }
        }

        // find all possible originals
        if ($this->getOriginals() && !$this->getOriginals()->isEmpty()) {
            foreach ($this->getOriginals() as $translation) {
                // if we already meet you stop and go on with the next
                if (array_key_exists($translation->getLocale(), $translationsArray)) {
                    return;
                }
                $translationsArray[$translation->getLocale()] = $translation;
                $translation->buildAllTranslations($translationsArray);
            }
        }
    }

    public static function getStatusList(): array
    {
        return [
            'status_draft' => self::STATUS_DRAFT,
            'status_review' => self::STATUS_REVIEW,
            'status_published' => self::STATUS_PUBLISHED,
        ];
    }

    public static function getVisibilityList(): array
    {
        return [
            'visibility_public' => self::VISIBILITY_PUBLIC,
            'visibility_protected' => self::VISIBILITY_PROTECTED,
        ];
    }

    public function getSnapshot(): ?PageSnapshotInterface
    {
        $pageSnapshots = $this->getSnapshots();

        return $pageSnapshots->first() ?: null;
    }

    public function hasPublishedVersion(): bool
    {
        return $this->getSnapshots()->count() > 0;
    }

    public function getSnapshotClassType(): string
    {
        return $this->snapshotClassType;
    }

    public function getCurrentVersion(): int
    {
        if ($this->getSnapshot()) {
            $version = $this->getSnapshot()->getVersion();

            return ++$version;
        }

        return 1;
    }

    public function getResourceId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function hasListener(): bool
    {
        return true;
    }

    public function convertParentToInteger(): ?int
    {
        if ($this->parent) {
            return $this->parent->getId();
        }

        return null;
    }

    public function convertAliasToInteger(): ?int
    {
        if ($this->alias) {
            return $this->alias->getId();
        }

        return null;
    }

    public function convertParentsToArray(): array
    {
        $parents = [];

        foreach ($this->getParents() as $parent) {
            $parents[] = $parent->getId();
        }

        return $parents;
    }

    public function convertChildrenToIntegerArray(): array
    {
        $children = [];

        foreach ($this->getChildren() as $child) {
            $children[] = $child->getId();
        }

        return $children;
    }

    public function convertTranslationsToArray(): array
    {
        $translations = [];

        foreach ($this->getAllTranslations() as $translation) {
            $translations[] = [
                'locale' => $translation->getLocale(),
                'id' => $translation->getId(),
                'contentRoute' => [
                    'id' => $translation->getContentRoute()->getId(),
                    'path' => $translation->getContentRoute()->getPath(),
                ],
            ];
        }

        return $translations;
    }

    public function convertOriginalsToArray(): array
    {
        $originals = [];

        foreach ($this->originals as $original) {
            $originals[] = [
                'locale' => $original->getLocale(),
                'id' => $original->getId(),
                'contentRoute' => [
                    'id' => $original->getContentRoute()->getId(),
                    'path' => $original->getContentRoute()->getPath(),
                ],
            ];
        }

        return $originals;
    }

    public function getStatusLabel(): string
    {
        if ($this->isPublished()) {
            return self::STATUS_PUBLISHED;
        }

        if ($this->isOffline()) {
            return self::STATUS_OFFLINE;
        }

        if ($this->isReview()) {
            return self::STATUS_REVIEW;
        }

        return self::STATUS_DRAFT;
    }

    public function getSocialMediaImage(): ?MediaInterface
    {
        return $this->socialMediaImage;
    }

    public function setSocialMediaImage(?MediaInterface $socialMediaImage): self
    {
        $this->socialMediaImage = $socialMediaImage;

        return $this;
    }

    public function restoreFromPublished(PageInterface $publishedPage): void
    {
        $this->createdAt = $publishedPage->getCreatedAt();
        $this->updatedAt = $publishedPage->getUpdatedAt();
        $this->pageName = $publishedPage->getPageName();
        $this->metaTitle = $publishedPage->getMetaTitle();
        $this->url = $publishedPage->getUrl();
        $this->status = $publishedPage->getStatus();
        $this->visibility = $publishedPage->getVisibility();
        $this->activeFrom = $publishedPage->getActiveFrom();
        $this->activeTo = $publishedPage->getActiveTo();
    }

    public function getContentRoute(): ContentRouteInterface
    {
        if (!$this->contentRoute) {
            $this->contentRoute = new ContentRoute();
        }

        return $this->contentRoute;
    }
}
