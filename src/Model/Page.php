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

namespace Networking\InitCmsBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Util\Urlizer;
use Networking\InitCmsBundle\Entity\LayoutBlock;
use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Class Page.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class Page implements PageInterface
{
    /**
     * @var int|string|null
     */
    protected $id;

    protected ?\DateTimeInterface $createdAt = null;

    #[Gedmo\Versioned()]
    protected ?\DateTimeInterface $updatedAt = null;

    protected ?string $pageName = null;

    protected ?string $metaTitle = null;

    #[Gedmo\TreePathSource()]
    protected ?string $url = null;

    #[Gedmo\TreePath(separator: "/")]
    protected ?string $path = null;

    #[Gedmo\TreeLevel()]
    protected ?int $level = null;

    protected ?string $metaKeyword = null;

    protected ?string $metaDescription = null;

    protected PageInterface|int|null $parent = null;

    protected array $parents = [];

    /**
     * @var Collection<int, PageInterface>
     */
    protected Collection|array $children;

    protected array $allChildren = [];

    protected ?PageInterface $alias = null;

    /**
     * @var Collection<int, LayoutBlockInterface>
     */
    protected Collection $layoutBlock;

    /**
     * @var Collection<int, MenuItemInterface>
     */
    protected Collection $menuItem;

    protected bool $isHome = false;

    protected string $status = self::STATUS_DRAFT;

    protected string $visibility = self::VISIBILITY_PUBLIC;

    protected ?\DateTimeInterface $activeFrom = null;

    protected ?\DateTimeInterface $activeTo = null;

    protected ?string $locale = null;

    /**
     * @var Collection<int, PageInterface>
     *
     * @phpstan-var Collection<int, T>
     */
    protected Collection|array $translations;

    /**
     * @var Collection<int, PageInterface>
     */
    protected Collection|array $originals;

    protected ?ContentRouteInterface $contentRoute = null;

    /**
     * @var Collection<int, PageSnapshotInterface>
     */
    protected Collection $snapshots;

    protected string $snapshotClassType = PageSnapshot::class;

    protected ?string $oldTitle = null;

    protected ?MediaInterface $socialMediaImage = null;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->originals = new ArrayCollection();
        $this->layoutBlock = new ArrayCollection();
        $this->menuItem = new ArrayCollection();
        $this->children = new ArrayCollection();


    }

    public function prePersist(): void
    {
        $this->createdAt = $this->updatedAt = new \DateTime('now');

        if (!$this->metaTitle) {
            $this->setMetaTitle($this->pageName);
        }
    }

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
     * @param $level
     *
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
     * @param PageInterface $children
     *
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

    /**
     * @param array $children
     *
     * @return array
     */
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
        return $this->status === self::STATUS_DRAFT;
    }

    public function isReview(): bool
    {
        return $this->status === self::STATUS_REVIEW;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isOffline(): bool
    {
        return $this->status == self::STATUS_OFFLINE;
    }

    public function isActive(): bool
    {
        $now = new \DateTime();

        if ($now->getTimestamp() >= $this->getActiveStart()->getTimestamp()
            && $now->getTimestamp() <= $this->getActiveEnd()->getTimestamp()
        ) {
            return $this->status == self::STATUS_PUBLISHED;
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
        $this->layoutBlock->add($layoutBlock);

        return $this;
    }

    public function removeLayoutBlock(LayoutBlockInterface $layoutBlock): self
    {
        $this->layoutBlock->removeElement($layoutBlock);

        return $this;
    }


    public function setLayoutBlock($layoutBlocks): self
    {
        if (gettype($layoutBlocks) == 'array') {
            $layoutBlocks = new ArrayCollection($layoutBlocks);
        }

        foreach ($layoutBlocks as $block) {
            $block->setPage($this);
        }

        $this->layoutBlock = $layoutBlocks;

        return $this;
    }

    public function orderLayoutBlocks(): void
    {
        $layoutBlocks = $this->layoutBlock->toArray();

        usort($layoutBlocks, [__CLASS__, 'compareSortOrder']);
        $this->layoutBlock = new ArrayCollection();
        foreach ($layoutBlocks as $layoutBlock) {
            $this->layoutBlock->add($layoutBlock);
        }
    }

    private function compareSortOrder(
        LayoutBlockInterface $a,
        LayoutBlockInterface $b
    ): int {
        return $a->getSortOrder() - $b->getSortOrder();
    }


    public function getLayoutBlock(?string $zone = null): Collection
    {
        if (!is_null($zone)) {
            return $this->layoutBlock->filter(
                function (LayoutBlockInterface $layoutBlock) use ($zone) {
                    return $layoutBlock->getZone() == $zone
                        && $layoutBlock->isActive();
                }
            );
        }

        return $this->layoutBlock;
    }

    public function setMenuItem(MenuItemInterface $menuItem): self
    {
        $menuItem->setPage($this);
        $this->menuItem = $menuItem;

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
        if (gettype($originals) == 'array') {
            $originals = new ArrayCollection($originals);
        }

        $this->originals = $originals;

        return $this;
    }

    public function setOriginal(PageInterface $page): self
    {
        $this->originals->add($page);

        return $this;
    }

    public function getOriginals(): Collection|array
    {
        return $this->originals;
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
        $page->setOriginal($this);

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
        if (gettype($translations) == 'array') {
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
     * @param $snapshots
     *
     * @return $this
     */
    public function setSnapshots($snapshots): self
    {
        if (gettype($snapshots) == 'array') {
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
        if (!$this->contentRoute) {
            return null;
        }

        return $this->getContentRoute()->getTemplateName();
    }

    public function getFullPath(): ?string
    {
        return $this->getContentRoute()?->getPath() ?? null;
    }

    public function getAliasFullPath(): ?string
    {
        if (!$this->getAlias()) {
            return null;
        }

        return $this->alias->getFullPath();
    }

    public function getAllTranslations(): Collection
    {
        $translationsArray = [];

        $this->buildAllTranslations($translationsArray);

        $allTranslations = new ArrayCollection($translationsArray);

        return $allTranslations;
    }


    public function buildAllTranslations(array &$translationsArray): void
    {
        // find all possible translations
        if ($this->getTranslations() && !$this->getTranslations()->isEmpty()) {
            foreach ($this->getTranslations() as $translation) {
                if ($translation) {
                    // if we already meet you stop and go on with the next
                    $translationsArray[$translation->getLocale()]
                        = $translation;
                    $translation->buildAllTranslations($translationsArray);
                }
            }
        }

        // find all possible originals
        if ($this->getOriginals() && !$this->getOriginals()->isEmpty()) {
            foreach ($this->getOriginals() as $translation) {
                // if we already meet you stop and go on with the next
                if (array_key_exists(
                    $translation->getLocale(),
                    $translationsArray
                )
                ) {
                    return;
                }
                $translationsArray[$translation->getLocale()] = $translation;
                $translation->buildAllTranslations($translationsArray);
            }
        }
    }

    public static function getStatusList(): array
    {
        $status = [
            'status_draft' => self::STATUS_DRAFT,
            'status_review' => self::STATUS_REVIEW,
            'status_published' => self::STATUS_PUBLISHED,
        ];

        return $status;
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

        return $pageSnapshots->first()?: null;
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

    public function convertTranslationsToIntegerArray(): array
    {
        $translations = [];

        foreach ($this->translations as $translation) {
            $translations[$translation->getLocale()] = $translation->getId();
        }

        return $translations;
    }

    public function convertOriginalsToIntegerArray(): array
    {
        $originals = [];

        foreach ($this->originals as $original) {
            $originals[$original->getLocale()] = $original->getId();
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
        $this->id = $publishedPage->getId();
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

}
