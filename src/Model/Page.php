<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Util\Urlizer;
use Networking\InitCmsBundle\Entity\LayoutBlock;
use Sonata\MediaBundle\Model\MediaInterface;

/**
 * Class Page.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class Page implements PageInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \Datetime
     */
    protected $createdAt;

    /**
     * @var \Datetime
     * @Gedmo\Versioned()
     */
    protected $updatedAt;

    /**
     * @var string
     */
    protected $pageName;

    /**
     * @var string
     */
    protected $metaTitle;

    /**
     * @var string
     * @Gedmo\TreePathSource()
     */
    protected $url;

    /**
     * @var string
     * @Gedmo\TreePath(separator="/")
     */
    protected $path;

    /**
     * @Gedmo\TreeLevel
     */
    protected $level;

    /**
     * @var string
     */
    protected $metaKeyword;

    /**
     * @var string
     */
    protected $metaDescription;

    /**
     * @var PageInterface|null
     */
    protected $parent;

    /**
     * @var array
     */
    protected $parents;

    /**
     * @var array
     */
    protected $children;

    /**
     * @var array
     */
    protected $allChildren = [];

    /**
     * @var PageInterface
     */
    protected $alias;

    /**
     * @var ArrayCollection
     */
    protected $layoutBlock;

    /**
     * @var ArrayCollection
     */
    protected $menuItem;

    /**
     * @var bool
     */
    protected $isHome = false;

    /**
     * @var string
     */
    protected $status = self::STATUS_DRAFT;

    /**
     * @var string
     */
    protected $visibility = self::VISIBILITY_PUBLIC;

    /**
     * @var \Datetime
     */
    protected $activeFrom;

    /**
     * @var \Datetime
     */
    protected $activeTo;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var ArrayCollection
     */
    protected $translations;

    /**
     * @var ArrayCollection
     */
    protected $originals;

    /**
     * @var ContentRouteInterface
     */
    protected $contentRoute;

    /**
     * @var ArrayCollection
     */
    protected $snapshots;

    /**
     * @var string
     */
    protected $snapshotClassType = 'Networking\InitCmsBundle\Entity\PageSnapshot';

    /**
     * @var string
     */
    protected $oldTitle;

    /**
     * @var MediaInterface|null
     */
    protected $socialMediaImage;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->originals = new ArrayCollection();
        $this->layoutBlock = new ArrayCollection();
        $this->menuItem = new ArrayCollection();


    }

    /**
     * Hook on to pre-persist action.
     */
    public function prePersist()
    {
        $this->createdAt = $this->updatedAt = new \DateTime('now');

        if (!$this->metaTitle) {
            $this->setMetaTitle($this->pageName);
        }
    }

    /**
     * Hook on to pre-update action.
     */
    public function preUpdate()
    {
        $this->setUpdatedAt();
    }

    /**
     * Set updatedAt.
     *
     * @return $this
     */
    public function setUpdatedAt()
    {
        $this->updatedAt = new \DateTime('now');

        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt.
     *
     * @param \Datetime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \Datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get updatedAt.
     *
     * @return \Datetime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->pageName;
    }

    /**
     * Set pageName.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setPageName($title)
    {
        $this->oldTitle = $this->pageName;
        $this->pageName = $title;

        if ($this->pageName && $this->getContentRoute()) {
            $this->getContentRoute()->setName($this->pageName);
        }

        return $this;
    }

    /**
     * Get pageName.
     *
     * @return string
     */
    public function getPageName()
    {
        return $this->pageName;
    }

    /**
     * Set metaTitle.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setMetaTitle($title)
    {
        $this->metaTitle = $title;

        return $this;
    }

    /**
     * Get metaTitle.
     *
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param $metaKeyword
     */
    public function setMetaKeyword($metaKeyword)
    {
        $this->metaKeyword = $metaKeyword;
    }

    /**
     * @return string
     */
    public function getMetaKeyword()
    {
        return $this->metaKeyword;
    }

    /**
     * @param $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @param PageInterface $parent
     *
     * @return $this
     */
    public function setParent(PageInterface $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @param $level
     *
     * @return PageInterface|null
     */
    public function getParent($level = -1)
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

    /**
     * @param PageInterface $alias
     */
    public function setAlias(PageInterface $alias = null)
    {
        $this->alias = $alias;
    }

    /**
     * @return PageInterface
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param array $parents
     *
     * @return $this
     */
    public function setParents(array $parents)
    {
        $this->parents = $parents;

        return $this;
    }

    /**
     * @return array
     */
    public function getParents()
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
    public function addChildren(PageInterface $children)
    {
        $this->children[] = $children;

        $children->setParent($this);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param $children
     *
     * @return $this
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @param array $children
     *
     * @return array
     */
    public function getAllChildren(&$children = [])
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

    /**
     * @param $children
     *
     * @return $this
     */
    public function setAllChildren($children)
    {
        $this->allChildren = $children;

        return $this;
    }

    /**
     * Set active.
     *
     * @param string $status
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setStatus($status)
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

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set page visibility.
     *
     * @param string $visibility
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setVisibility($visibility)
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

    /**
     * Get page visibility.
     *
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @return bool
     */
    public function isDraft()
    {
        return $this->status == self::STATUS_DRAFT;
    }

    /**
     * @return bool
     */
    public function isReview()
    {
        return $this->status == self::STATUS_REVIEW;
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return $this->status == self::STATUS_PUBLISHED;
    }

    /**
     * @return bool
     */
    public function isOffline()
    {
        return $this->status == self::STATUS_OFFLINE;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        $now = new \DateTime();

        if ($now->getTimestamp() >= $this->getActiveStart()->getTimestamp()
            && $now->getTimestamp() <= $this->getActiveEnd()->getTimestamp()
        ) {
            return $this->status == self::STATUS_PUBLISHED;
        }

        return false;
    }

    /**
     * Set activeFrom.
     *
     * @param  $activeFrom
     *
     * @return $this
     */
    public function setActiveFrom($activeFrom)
    {
        $this->activeFrom = $activeFrom;

        return $this;
    }

    /**
     * Get activeFrom.
     *
     * @return \DateTime
     */
    public function getActiveFrom()
    {
        return $this->activeFrom;
    }

    /**
     * Get activeFrom.
     *
     * @return \DateTime
     */
    public function getActiveStart()
    {
        if (!$this->activeFrom) {
            return new \DateTime();
        }

        return $this->activeFrom;
    }

    /**
     * @param \Datetime $activeTo
     */
    public function setActiveTo($activeTo)
    {
        $this->activeTo = $activeTo;
    }

    /**
     * @return \Datetime
     */
    public function getActiveTo()
    {
        return $this->activeTo;
    }

    /**
     * @return \Datetime
     */
    public function getActiveEnd()
    {
        if (!$this->activeTo) {
            return new \DateTime();
        }

        return $this->activeTo;
    }

    /**
     * Add layout block.
     *
     * @param LayoutBlockInterface $layoutBlock
     *
     * @return $this
     */
    public function addLayoutBlock(LayoutBlockInterface $layoutBlock)
    {
        $layoutBlock->setPage($this);
        $this->layoutBlock->add($layoutBlock);

        return $this;
    }

    /**
     * remove content.
     *
     * @param LayoutBlockInterface $layoutBlock
     *
     * @return $this
     */
    public function removeLayoutBlock(LayoutBlockInterface $layoutBlock)
    {
        $this->layoutBlock->removeElement($layoutBlock);

        return $this;
    }


    /**
     * @param $layoutBlocks
     *
     * @return $this
     */
    public function setLayoutBlock($layoutBlocks)
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

    public function orderLayoutBlocks()
    {
        $layoutBlocks = $this->layoutBlock->toArray();

        usort($layoutBlocks, [__CLASS__, 'compareSortOrder']);
        $this->layoutBlock = new ArrayCollection();
        foreach ($layoutBlocks as $layoutBlock) {
            $this->layoutBlock->add($layoutBlock);
        }
    }

    /**
     * @param $a
     * @param $b
     *
     * @return int
     */
    private function compareSortOrder($a, $b)
    {
        return $a->getSortOrder() - $b->getSortOrder();
    }

    /**
     * Get menuItem.
     *
     * @param null $zone
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|\Doctrine\Common\Collections\Collection
     */
    public function getLayoutBlock($zone = null)
    {
        if (!is_null($zone)) {
            $layoutBlocks = $this->layoutBlock->filter(
                function ($layoutBlock) use ($zone) {
                    return $layoutBlock->getZone() == $zone
                        && $layoutBlock->isActive();
                }
            );

            return $layoutBlocks;
        }

        return $this->layoutBlock;
    }

    /**
     * Add menuItem.
     *
     * @param MenuItemInterface $menuItem
     *
     * @return $this
     */
    public function setMenuItem(MenuItemInterface $menuItem)
    {
        $menuItem->setPage($this);
        $this->menuItem = $menuItem;

        return $this;
    }

    /**
     * remove menuItem.
     *
     * @param MenuItemInterface $menuItem
     *
     * @return $this
     */
    public function removeMenuItem(MenuItemInterface $menuItem)
    {
        $this->menuItem->removeElement($menuItem);

        return $this;
    }

    /**
     * Get menuItem.
     *
     * @return ArrayCollection
     */
    public function getMenuItem()
    {
        return $this->menuItem;
    }

    /**
     * @param $rootId
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMenuItemByRoot($rootId)
    {
        return $this->menuItem->filter(
            function ($menuItem) use ($rootId) {
                return $menuItem->getRoot() == $rootId;
            }
        );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!$this->pageName) {
            if ($this->oldTitle) {
                return $this->oldTitle;
            }

            return '-------';
        }

        return $this->pageName;
    }

    /**
     * @param $isHome
     *
     * @return $this
     */
    public function setIsHome($isHome)
    {
        $this->isHome = $isHome;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsHome()
    {
        return $this->isHome;
    }

    /**
     * @return bool
     */
    public function isHome()
    {
        return $this->isHome;
    }

    /**
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        $this->getContentRoute()->setLocale($locale);

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param array $originals
     *
     * @return $this
     */
    public function setOriginals($originals)
    {
        if (gettype($originals) == 'array') {
            $originals = new ArrayCollection($originals);
        }

        $this->originals = $originals;

        return $this;
    }

    /**
     * @param PageInterface $page
     *
     * @return $this
     */
    public function setOriginal(PageInterface $page)
    {
        $this->originals->add($page);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getOriginals()
    {
        return $this->originals;
    }

    /**
     * @param PageInterface $page
     *
     * @return bool
     */
    public function isDirectTranslation(PageInterface $page)
    {
        if ($this->originals->contains($page)) {
            return true;
        } elseif ($this->translations->contains($page)) {
            return true;
        }

        return false;
    }

    /**
     * @param PageInterface $page
     *
     * @return mixed
     */
    public function getDirectTranslationFor(PageInterface $page)
    {
        foreach ($this->getAllTranslations() as $translation) {
            if ($translation->isDirectTranslation($page)) {
                return $translation;
            }
        }
    }

    /**
     * @param PageInterface $page
     *
     * @return $this
     */
    public function addTranslation(PageInterface $page)
    {
        $this->translations->add($page);
        $page->setOriginal($this);

        return $this;
    }

    /**
     * @param PageInterface $page
     *
     * @return $this
     */
    public function removeTranslation(PageInterface $page)
    {
        $this->translations->removeElement($page);
        $page->getOriginals()->removeElement($this);

        return $this;
    }

    /**
     * @param array|ArrayCollection $translations
     *
     * @return $this
     */
    public function setTranslations($translations)
    {
        if (gettype($translations) == 'array') {
            $translations = new ArrayCollection($translations);
        }

        $this->translations = $translations;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        if (!$this->translations) {
            $this->translations = new ArrayCollection();
        }

        return $this->translations;
    }

    /**
     * @return array
     */
    public function getTranslatedLocales()
    {
        $locales = [];

        if ($this->getAllTranslations()->count()) {
            foreach ($this->getAllTranslations() as $translation) {
                $locales[] = $translation->getLocale();
            }
        }

        return $locales;
    }

    /**
     * @return string
     */
    public function getAdminTitle()
    {
        $countParents = count($this->getParents());
        $prefix = '';
        for ($i = 0; $i < $countParents; ++$i) {
            $prefix .= '- ';
        }

        return $prefix.''.$this->getPageName();
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $url = Urlizer::urlize($url);
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param ContentRouteInterface $contentRoute
     *
     * @return $this
     */
    public function setContentRoute(ContentRouteInterface $contentRoute)
    {
        $this->contentRoute = $contentRoute;

        return $this;
    }

    /**
     * @param $snapshots
     *
     * @return $this
     */
    public function setSnapshots($snapshots)
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
     * @return \Doctrine\Common\Collections\ArrayCollection $snapshots
     */
    public function getSnapshots()
    {
        if (!$this->snapshots) {
            return new ArrayCollection();
        }

        return $this->snapshots;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoute()
    {
        return ContentRouteManager::generateRoute(
            $this->contentRoute,
            $this->contentRoute->getPath(),
            $this
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes()
    {
        return [$this->getRoute()];
    }

    /**
     * @param $template
     *
     * @return $this
     */
    public function setTemplate($template)
    {
        if (!$this->id) {
            $this->getContentRoute()->setClassType(get_class($this));
            $this->getContentRoute()->setLocale($this->getLocale());
        }
        $this->getContentRoute()->setTemplate($template);

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        if (!$this->contentRoute) {
            return;
        }

        return $this->contentRoute->getTemplate();
    }

    /**
     * @param $templateName
     *
     * @return $this
     */
    public function setTemplateName($templateName)
    {
        if (!$this->id) {
            $this->getContentRoute()->setClassType(get_class($this));
            $this->getContentRoute()->setLocale($this->getLocale());
        }
        $this->getContentRoute()->setTemplateName($templateName);

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateName()
    {
        if (!$this->contentRoute) {
            return;
        }

        return $this->getContentRoute()->getTemplateName();
    }

    /**
     * @return string
     */
    public function getFullPath()
    {
        return $this->getContentRoute()->getPath();
    }

    /**
     * @return string
     */
    public function getAliasFullPath()
    {
        if (!$this->getAlias()) {
            return '';
        }

        return $this->alias->getFullPath();
    }

    /**
     * @return ArrayCollection
     */
    public function getAllTranslations()
    {
        $translationsArray = [];

        $this->getRecursiveTranslations($translationsArray);

        $allTranslations = new ArrayCollection($translationsArray);

        return $allTranslations;
    }

    /**
     * Recursively search for all possible translations of this page, either originals
     * of this page, translations of this page or translations of the original of this page.
     *
     * @param array $translationsArray
     *
     * @return array
     */
    public function getRecursiveTranslations(&$translationsArray)
    {
        // find all possible translations
        if ($this->getTranslations() && !$this->getTranslations()->isEmpty()) {
            foreach ($this->getTranslations() as $translation) {
                if ($translation) {
                    // if we already meet you stop and go on with the next
                    $translationsArray[$translation->getLocale()]
                        = $translation;
                    $translation->getRecursiveTranslations($translationsArray);
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
                $translation->getRecursiveTranslations($translationsArray);
            }
        }
    }

    /**
     * @return array
     */
    public static function getStatusList()
    {
        $status = [
            'status_draft' => self::STATUS_DRAFT,
            'status_review' => self::STATUS_REVIEW,
            'status_published' => self::STATUS_PUBLISHED,
        ];

        return $status;
    }

    /**
     * @return array
     */
    public static function getVisibilityList()
    {
        return [
            'visibility_public' => self::VISIBILITY_PUBLIC,
            'visibility_protected' => self::VISIBILITY_PROTECTED,
        ];
    }

    /**
     * @return mixed|PageSnapshotInterface
     */
    public function getSnapshot()
    {
        $pageSnapshots = $this->getSnapshots();

        return $pageSnapshots->first();
    }

    public function hasPublishedVersion()
    {
        if ($this->getSnapshots()->count() > 0) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed|string
     */
    public function getSnapshotClassType()
    {
        return $this->snapshotClassType;
    }

    /**
     * @return int
     */
    public function getCurrentVersion()
    {
        if ($this->getSnapshot()) {
            $version = $this->getSnapshot()->getVersion();

            return ++$version;
        } else {
            return 1;
        }
    }

    /**
     * @return int
     */
    public function getResourceId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function hasListener()
    {
        return 'Networking\InitCmsBundle\EventListener\PageListener';
    }

    /**
     * @return int
     */
    public function convertParentToInteger()
    {
        if ($this->parent) {
            return $this->parent->getId();
        }

        return 0;
    }

    /**
     * @return int
     */
    public function convertAliasToInteger()
    {
        if ($this->alias) {
            return $this->alias->getId();
        }

        return null;
    }

    /**
     * @param $id
     *
     * @return $this|null
     */
    public function convertIntegerToPage($id)
    {
        $page = null;

        return $page;
    }

    /**
     * @return array
     */
    public function convertParentsToArray()
    {
        $parents = [];

        foreach ($this->getParents() as $parent) {
            $parents[] = $parent->getId();
        }

        return $parents;
    }

    /**
     * @return array
     */
    public function convertChildrenToIntegerArray()
    {
        $children = [];

        foreach ($this->getChildren() as $child) {
            $children[] = $child->getId();
        }

        return $children;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function prepareMenuItemsForSerialization()
    {
        return $this->getMenuItem();
    }

    /**
     * @return array
     */
    public function convertTranslationsToIntegerArray()
    {
        $translations = [];

        foreach ($this->translations as $translation) {
            $translations[$translation->getLocale()] = $translation->getId();
        }

        return $translations;
    }

    /**
     * @return array
     */
    public function convertOriginalsToIntegerArray()
    {
        $originals = [];

        foreach ($this->originals as $original) {
            $originals[$original->getLocale()] = $original->getId();
        }

        return $originals;
    }

    /**
     * @return string
     */
    public function getStatusLabel()
    {
        if ($this->isPublished()) {
            return self::STATUS_PUBLISHED;
        }

        if ($this->isOffline()) {
            return self::STATUS_OFFLINE;
        }

        return self::STATUS_DRAFT;
    }

    /**
     * @return MediaInterface|null
     */
    public function getSocialMediaImage(): ?MediaInterface
    {
        return $this->socialMediaImage;
    }

    /**
     * @param MediaInterface|null $socialMediaImage
     *
     * @return Page
     */
    public function setSocialMediaImage(?MediaInterface $socialMediaImage): Page
    {
        $this->socialMediaImage = $socialMediaImage;

        return $this;
    }

    public function restoreFromPublished(PageInterface $publishedPage)
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
