<?php

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
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\Common\Collections\ArrayCollection;

use Networking\InitCmsBundle\Entity\LayoutBlock;
use Networking\InitCmsBundle\Entity\Tag;
use Networking\InitCmsBundle\Entity\ContentRoute;
use Networking\InitCmsBundle\Entity\MenuItem;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface;

use Symfony\Component\Validator\Constraints as Assert;

use Gedmo\Mapping\Annotation as Gedmo;

use Symfony\Cmf\Component\Routing\RouteAwareInterface;

/**
 * Networking\InitCmsBundle\Entity\Page
 * @Gedmo\Tree(type="materializedPath")
 * @Gedmo\Loggable
 *
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="page", uniqueConstraints={@ORM\UniqueConstraint(name="path_idx", columns={"path", "locale"})})
 * @ORM\Entity(repositoryClass="Networking\InitCmsBundle\Entity\PageRepository")
 *
 * @author net working AG <info@networking.ch>
 */
class Page implements RouteAwareInterface, VersionableInterface
{

    /**
     *
     */
    const PATH_SEPARATOR = '/';

    /**
     *
     */
    const VISIBILITY_PUBLIC = 'public';

    /**
     *
     */
    const VISIBILITY_PROTECTED = 'protected';

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \Datetime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \Datetime $updatedAt
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @var string $pageName
     * @ORM\Column(name="page_name", type="string", length=255)
     * @Assert\NotBlank(groups={"default"})
     */
    protected $pageName;

    /**
     * @var string $metaTitle
     * @ORM\Column(name="meta_title", type="string", length=255, nullable=true)
     */
    protected $metaTitle;

    /**
     * @var string $url
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"not_home"})
     */
    protected $url;

    /**
     * @var string $slug
     * @Gedmo\TreePathSource
     * @Gedmo\Slug(fields={"url"}, separator="-", updatable=true, unique=false)
     * @ORM\Column(name="slug", type="string", length=255, nullable=true)
     */
    protected $slug;

    /**
     * @var string $path
     * @Gedmo\TreePath(separator="/")
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer", nullable=true)
     */
    protected $level;

    /**
     * @var string $metaKeyword
     * @ORM\Column(name="meta_keyword", type="string", length=255, nullable=true)
     */
    protected $metaKeyword;

    /**
     * @var string $metaDescription
     * @ORM\Column(name="meta_description", type="text", nullable=true)
     */
    protected $metaDescription;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @var array $parents
     */
    protected $parents;

    /**
     * @ORM\OneToMany(targetEntity="Page", mappedBy="parent")
     */
    protected $children;

    /**
     * @var array $allChildren
     */
    protected $allChildren = array();


    /**
     * @ORM\OneToMany(targetEntity="Networking\InitCmsBundle\Entity\LayoutBlock", cascade={"persist", "detach"}, mappedBy="page", orphanRemoval=true)
     * @OrderBy({"sortOrder" = "ASC"})
     *
     */
    protected $layoutBlock;

    /**
     * @ORM\OneToMany(targetEntity="Networking\InitCmsBundle\Entity\MenuItem", mappedBy="page", cascade={"all"}, orphanRemoval=true)
     */
    protected $menuItem;

    /**
     * @var boolean $isHome
     * @ORM\Column(name="is_home", type="boolean", nullable=true)
     */
    protected $isHome = false;

    /**
     * @var string $status
     * @ORM\Column(name="status", type="string", length=50)
     */
    protected $status = self::STATUS_DRAFT;

    /**
     * @var string $visibility
     *
     * @ORM\Column(name="visibility", type="string", length=50)
     */
    protected $visibility = self::VISIBILITY_PUBLIC;

    /**
     * @var \Datetime $activeFrom
     *
     * @ORM\Column(name="active_from", type="date", nullable=true)
     */
    protected $activeFrom;

    /**
     * @var string $locale
     * @ORM\Column(name="locale", type="string", length=5)
     */
    protected $locale;

    /**
     * @var ArrayCollection $translations
     * @ORM\ManyToMany(targetEntity="Page", mappedBy="originals")
     */
    protected $translations;

    /**
     * @var ArrayCollection $originals
     * @ORM\ManyToMany(targetEntity="Page", inversedBy="translations", cascade={"persist"})
     * @ORM\JoinTable(name="page_translation",
     *      joinColumns={@ORM\JoinColumn(name="translation_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="original_id", referencedColumnName="id")}
     *      )
     */
    protected $originals;

    /**
     * @var ArrayCollection $tags
     * @ORM\ManyToMany(targetEntity="Tag")
     * @ORM\JoinTable(name="page_tags")
     * @OrderBy({"name" = "ASC"})
     */
    protected $tags;

    /**
     * @var ContentRoute $contentRoute
     * @ORM\OneToOne(targetEntity="Networking\InitCmsBundle\Entity\ContentRoute", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="content_route_id")
     */
    protected $contentRoute;

    /**
     * @var ArrayCollection $snapshots
     *
     * @ORM\OneToMany(targetEntity="Networking\InitCmsBundle\Entity\PageSnapshot", mappedBy="page", cascade={"remove"})
     * @OrderBy({"version" = "DESC"})
     */
    protected $snapshots;

    /**
     * @var string
     */
    protected $snapshotClassType = 'Networking\InitCmsBundle\Entity\PageSnapshot';

    /**
     * @var string $oldTitle
     */
    protected $oldTitle;

    /**
     *
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->originals = new ArrayCollection();
        $this->layoutBlock = new ArrayCollection();
        $this->menuItem = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = $this->updatedAt = new \DateTime("now");

        if(!$this->metaTitle){
            $this->setMetaTitle($this->pageName);
        }
    }

    /**
     * Set updatedAt
     *
     * @ORM\PreUpdate
     * @return Page
     */
    public function setUpdatedAt()
    {
        $this->updatedAt = new \DateTime("now");

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param  \Datetime $createdAt
     * @return Page
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \Datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get updatedAt
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
     * Set pageName
     *
     * @param  string $title
     * @return Page
     */
    public function setPageName($title)
    {
        $this->oldTitle = $this->pageName;
        $this->pageName = $title;

        return $this;
    }

    /**
     * Get pageName
     *
     * @return string
     */
    public function getPageName()
    {
        return $this->pageName;
    }

    /**
     * Set metaTitle
     *
     * @param  string $title
     * @return Page
     */
    public function setMetaTitle($title)
    {
        $this->metaTitle = $title;

        return $this;
    }

    /**
     * Get metaTitle
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
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @param  Page $parent
     * @return Page
     */
    public function setParent(Page $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @param $level
     * @return null
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
     * @param  array $parents
     * @return Page
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
            $parents = array();

            while ($page->getParent()) {
                $page = $page->getParent();
                $parents[] = $page;
            }

            $this->setParents(array_reverse($parents));
        }

        return $this->parents;
    }

    /**
     * @param  Page $children
     * @return Page
     */
    public function addChildren(Page $children)
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
     * @return Page
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @param  array $children
     * @return array
     */
    public function getAllChildren(&$children = array())
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
     * @return Page
     */
    public function setAllChildren($children)
    {
        $this->allChildren = $children;

        return $this;
    }

    /**
     * Set active
     *
     * @param string $status
     * @return Page
     */
    public function setStatus($status)
    {
        if (!in_array($status, array(self::STATUS_DRAFT, self::STATUS_PUBLISHED, self::STATUS_REVIEW))) {
            throw new \InvalidArgumentException("Invalid status");
        }
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set page visibility
     *
     * @param string $visibility
     * @return Page
     */
    public function setVisibility($visibility)
    {
        if (!in_array($visibility, array(self::VISIBILITY_PROTECTED, self::VISIBILITY_PUBLIC))) {
            throw new \InvalidArgumentException("Invalid visibility");
        }
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Get page visibility
     *
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     *
     * @return bool
     */
    public function isDraft()
    {
        return ($this->status == self::STATUS_DRAFT);
    }

    /**
     *
     * @return bool
     */
    public function isReview()
    {
        return ($this->status == self::STATUS_REVIEW);
    }

    /**
     *
     * @return bool
     */
    public function isPublished()
    {
        return ($this->status == self::STATUS_PUBLISHED);
    }

    /**
     *
     * @return bool
     * @deprecated please use isPublished
     */
    public function isActive()
    {
        return ($this->status == self::STATUS_PUBLISHED);
    }

    /**
     * Set activeFrom
     *
     * @param  date $activeFrom
     * @return Page
     */
    public function setActiveFrom($activeFrom)
    {
        $this->activeFrom = $activeFrom;

        return $this;
    }

    /**
     * Get activeFrom
     *
     * @return date
     */
    public function getActiveFrom()
    {
        if (!$this->activeFrom) return new \DateTime();

        return $this->activeFrom;
    }

    /**
     * Add layout block
     *
     * @param  LayoutBlock $layoutBlock
     * @return Page
     */
    public function addLayoutBlock(LayoutBlock $layoutBlock)
    {
        $layoutBlock->setPage($this);
        $this->layoutBlock->add($layoutBlock);

        return $this;
    }

    /**
     * remove content
     *
     * @param  LayoutBlock $layoutBlock
     * @return Page
     */
    public function removeLayoutBlock(LayoutBlock $layoutBlock)
    {
        $this->layoutBlock->removeElement($layoutBlock);

        return $this;
    }

    /**
     * Remove all layout blocks from page object (and DB)
     * reset the layout blocks with new ones or from a published page
     *
     * @param $publishedBlocks
     */
    public function resetLayoutBlock($publishedBlocks)
    {
        foreach ($this->layoutBlock as $block) {
            $block->setIsSnapshot(true);
            $this->layoutBlock->removeElement($block);
        }

        $this->setLayoutBlock($publishedBlocks);

    }

    /**
     * @param $layoutBlocks
     * @return Page
     */
    public function setLayoutBlock($layoutBlocks)
    {

        if (gettype($layoutBlocks) == "array") {
            $layoutBlocks = new ArrayCollection($layoutBlocks);
        }

        foreach ($layoutBlocks as $content) {
            $content->setPage($this);
        }

        $this->layoutBlock = $layoutBlocks;

        return $this;
    }

    /**
     *
     */
    public function orderLayoutBlocks()
    {
        $layoutBlocks = $this->layoutBlock->toArray();

        usort($layoutBlocks, array(__CLASS__, 'compareSortOrder'));
        $this->layoutBlock = new ArrayCollection();
        foreach ($layoutBlocks as $layoutBlock) {
            $this->layoutBlock->add($layoutBlock);
        }
    }

    /**
     * @param $a
     * @param $b
     * @return int
     */
    private function compareSortOrder($a, $b)
    {
        return strcmp($a->getSortOrder(), $b->getSortOrder());
    }

    /**
     * Get menuItem
     *
     * @param  null $zone
     * @return \Doctrine\Common\Collections\ArrayCollection|\Doctrine\Common\Collections\Collection
     */
    public function getLayoutBlock($zone = null)
    {
        if (!is_null($zone)) {
            return $this->layoutBlock->filter(function ($layoutBlock) use ($zone) {
                return ($layoutBlock->getZone() == $zone && $layoutBlock->isActive());
            });
        }

        return $this->layoutBlock;
    }

    /**
     * Add menuItem
     *
     * @param  MenuItem $menuItem
     * @return Page
     */
    public function setMenuItem(MenuItem $menuItem)
    {

        $menuItem->setPage($this);
        $this->menuItem = $menuItem;

        return $this;
    }

    /**
     * remove menuItem
     *
     * @param  MenuItem $menuItem
     * @return Page
     */
    public function removeMenuItem(MenuItem $menuItem)
    {
        $this->menuItem->removeElement($menuItem);

        return $this;
    }

    /**
     * Get menuItem
     *
     * @return ArrayCollection
     */
    public function getMenuItem()
    {
        return $this->menuItem;
    }

    /**
     * @param $rootId
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMenuItemByRoot($rootId)
    {
        return $this->menuItem->filter(function ($menuItem) use ($rootId) {
            return ($menuItem->getRoot() == $rootId);
        });
    }

    /**
     * Add tags
     *
     * @param \Networking\InitCmsBundle\Entity\Tag  $tag
     * @return Page
     */
    public function addTags(Tag $tag)
    {
        $this->tags->add($tag);

        return $this;
    }

    /**
     * @param  \Doctrine\Common\Collections\ArrayCollection $tags
     * @return Page
     */
    public function setTags(ArrayCollection $tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Get tags
     *
     * @return ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
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
     * @return Page
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
     * @param  string $locale
     * @return Page
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

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
     * @param  array $originals
     * @return Page
     */
    public function setOriginals(array $originals)
    {
        if (gettype($originals) == "array") {
            $originals = new ArrayCollection($originals);
        }

        $this->originals = $originals;

        return $this;
    }

    /**
     * @param  Page $page
     * @return Page
     */
    public function setOriginal(Page $page)
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
     * @param Page $page
     * @return bool
     */
    public function isDirectTranslation(Page $page)
    {
        if ($this->originals->contains($page)) {
            return true;
        } elseif ($this->translations->contains($page)) {
            return true;
        }

        return false;
    }

    /**
     * @param Page $page
     * @return mixed
     */
    public function getDirectTranslationFor(Page $page)
    {
        foreach ($this->getAllTranslations() as $translation) {
            if ($translation->isDirectTranslation($page)) {
                return $translation;
            }
        }
    }

    /**
     * @param Page $page
     * @return Page
     */
    public function addTranslation(Page $page)
    {
        $this->translations->add($page);
        $page->setOriginal($this);

        return $this;
    }

    /**
     * @param Page $page
     * @return Page
     */
    public function removeTranslation(Page $page)
    {
        $this->translations->removeElement($page);
        $page->getOriginals()->removeElement($this);

        return $this;
    }


    /**
     * @param  array $translations
     * @return page
     */
    public function setTranslations(array $translations)
    {

        if (gettype($translations) == "array") {
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
        return $this->translations;
    }

    /**
     * @return array
     */
    public function getTranslatedLocales()
    {
        $locales = array();

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
        for ($i = 0; $i < $countParents; $i++) {
            $prefix .= '- ';
        }

        return $prefix . '' . $this->getPageName();
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
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
     * @param  ContentRoute $contentRoute
     * @return Page
     */
    public function setContentRoute(ContentRoute $contentRoute)
    {
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
     * @param $snapshots
     * @return Page
     */
    public function setSnapshots($snapshots)
    {
        if (gettype($snapshots) == "array") {
            $snapshots = new ArrayCollection($snapshots);
        } elseif(! $snapshots instanceof ArrayCollection){
            $snapshots = new ArrayCollection(array($snapshots));
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
        return $this->snapshots;
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

    /**
     * @param $template
     * @return Page
     */
    public function setTemplate($template)
    {
        if (!$this->contentRoute) {
            $contentRoute = new ContentRoute();
            $contentRoute->setClassType(get_class($this));
            $contentRoute->setLocale($this->getLocale());

            $this->setContentRoute($contentRoute);
        }
        $this->contentRoute->setTemplate($template);

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        if (!$this->contentRoute) return;
        return $this->contentRoute->getTemplate();
    }

    /**
     * @return string
     */
    public function getFullPath()
    {
        return $this->contentRoute->getPath();
    }

    /**
     * @return ArrayCollection
     */
    public function getAllTranslations()
    {

        $translationsArray = array();

        $this->getRecursiveTranslations($translationsArray);

        $allTranslations = new ArrayCollection($translationsArray);

        return $allTranslations;
    }

    /**
     * Recursively search for all possible translations of this page, either originals
     * of this page, translations of this page or translations of the original of this page.
     *
     * @param  array $translationsArray
     * @return array
     */
    public function getRecursiveTranslations(&$translationsArray)
    {
        // find all possible translations
        if (!$this->getTranslations()->isEmpty()) {
            foreach ($this->getTranslations() as $translation) {
                // if we already meet you stop and go on with the next
                $translationsArray[$translation->getLocale()] = $translation;
                $translation->getRecursiveTranslations($translationsArray);
            }

        }

        // find all possible originals
        if (!$this->getOriginals()->isEmpty()) {
            foreach ($this->getOriginals() as $translation) {
                // if we already meet you stop and go on with the next
                if (array_key_exists($translation->getLocale(), $translationsArray)) return;
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

        $status = array(
            self::STATUS_DRAFT => 'status_draft',
            self::STATUS_REVIEW => 'status_review',
            self::STATUS_PUBLISHED => 'status_published'
        );

        return $status;
    }

    /**
     * @return array
     */
    public static function getVisibilityList()
    {
        return array(
            self::VISIBILITY_PUBLIC => 'visibility_public',
            self::VISIBILITY_PROTECTED => 'visibility_protected'
        );
    }

    /**
     * @return PageSnapshot
     */
    public function getSnapshot()
    {
        $pageSnapshots = $this->getSnapshots();

        return $pageSnapshots->first();
    }

    public function hasPublishedVersion()
    {
        if($this->getSnapshots()->count() > 0){
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
     * @param $id
     * @return Page|null
     */
    public function convertIntegerToPage($id)
    {
        $page = null;

        if ($id) {
            $page = new Page();
        }

        return $page;
    }

    /**
     * @return array
     */
    public function convertParentsToArray()
    {
        if (!is_array($this->parents)) {
            $this->parents = array();
        }

        return $this->parents;
    }

    /**
     * @return array
     */
    public function convertChildrenToIntegerArray()
    {
        $children = array();

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
        $translations = array();

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
        $originals = array();

        foreach ($this->originals as $original) {
            $originals[$original->getLocale()] = $original->getId();
        }

        return $originals;
    }

    public function getStatusLabel()
    {
        if($this->isPublished()){
            return self::STATUS_PUBLISHED;
        }
        return self::STATUS_DRAFT;
    }
}
