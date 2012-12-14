<?php

namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\Common\Collections\ArrayCollection;

use Networking\InitCmsBundle\Entity\LayoutBlock;
use Networking\InitCmsBundle\Entity\Tag;
use Networking\InitCmsBundle\Entity\ContentRoute;
use Networking\InitCmsBundle\Entity\MenuItem;

use Symfony\Component\Validator\Constraints as Assert;

use Gedmo\Mapping\Annotation as Gedmo;

use Symfony\Cmf\Component\Routing\RouteAwareInterface;

/**
 * Networking\InitCmsBundle\Entity\Page
 * @Gedmo\Tree(type="materializedPath")
 *
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="page", uniqueConstraints={@ORM\UniqueConstraint(name="path_idx", columns={"path", "locale"})})
 * @ORM\Entity(repositoryClass="Networking\InitCmsBundle\Entity\PageRepository")
 */
class Page implements RouteAwareInterface
{

	const PATH_SEPARATOR = '/';

	const STATUS_DRAFT = 'draft';

	const STATUS_REVIEW = 'review';

	const STATUS_PUBLISHED = 'published';

	const VISIBILITY_PUBLIC = 'public';

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
	 * @var string $title
	 * @ORM\Column(name="title", type="string", length=255)
	 * @Assert\NotBlank()
	 */
	protected $title;

	/**
	 * @var string $url
	 * @ORM\Column(name="url", type="string", length=255, nullable=true)
	 */
	protected $url;

	/**
	 * @var string $slug
	 *
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
	 *
	 * @ORM\Column(name="meta_keyword", type="string", length=255)
	 */
	protected $metaKeyword;

	/**
	 * @var string $metaDescription
	 *
	 * @ORM\Column(name="meta_description", type="text")
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
	 * @var string $navigationTitle
	 *
	 * @ORM\Column(name="navigation_title", type="string", length=255, nullable=true)
	 */
	protected $navigationTitle;

	/**
	 * @ORM\OneToMany(targetEntity="LayoutBlock", mappedBy="page", cascade={"persist"}, orphanRemoval=true)
	 * @OrderBy({"sortOrder" = "ASC"})
	 */
	protected $layoutBlock;

	/**
	 * @ORM\OneToMany(targetEntity="Networking\InitCmsBundle\Entity\MenuItem", mappedBy="page", cascade={"all"}, orphanRemoval=true)
	 */
	protected $menuItem;

	/**
	 * @var boolean $isHome
	 *
	 * @ORM\Column(name="isHome", type="boolean", nullable=true)
	 */
	protected $isHome = false;

	/**
	 * @var string $status
	 *
	 * @ORM\Column(name="status", type="string", columnDefinition="ENUM('draft', 'review', 'published') NOT NULL")
	 */
	protected $status = self::STATUS_DRAFT;

	/**
	 * @var string $visibility
	 *
	 * @ORM\Column(name="visibility", type="string", columnDefinition="ENUM('public', 'protected') NOT NULL")
	 */
	protected $visibility = self::VISIBILITY_PUBLIC;

	/**
	 * @var \Datetime $activeFrom
	 *
	 * @ORM\Column(name="activeFrom", type="date")
	 */
	protected $activeFrom;

	/**
	 * @var \Datetime $activeTill
	 *
	 * @ORM\Column(name="activeTill", type="date", nullable=true)
	 */
	protected $activeTill;

	/**
	 * @var string $locale
	 *
	 * @ORM\Column(name="locale", type="string", length=5)
	 */
	protected $locale;

	/**
	 * @var ArrayCollection $translations
	 *
	 * @ORM\ManyToMany(targetEntity="Page", mappedBy="originals")
	 */
	protected $translations;

	/**
	 * @var Page $original
	 *
	 * @ORM\ManyToMany(targetEntity="Page", inversedBy="translations", cascade={"persist"})
	 * @ORM\JoinTable(name="page_translation",
	 *      joinColumns={@ORM\JoinColumn(name="translation_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="original_id", referencedColumnName="id")}
	 *      )
	 */
	protected $originals;

	/**
	 * @var ArrayCollection $tags
	 *
	 * @ORM\ManyToMany(targetEntity="Tag", inversedBy="pages")
	 * @ORM\JoinTable(name="page_tags")
	 * @OrderBy({"name" = "ASC"})
	 */
	protected $tags;

	/**
	 * @var ContentRoute $contentRoute
	 *
	 * @ORM\OneToOne(targetEntity="Networking\InitCmsBundle\Entity\ContentRoute", cascade={"persist"})
	 * @ORM\JoinColumn(name="content_route_id")
	 */
	protected $contentRoute;

	protected $oldTitle;

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
	 * Set title
	 *
	 * @param  string $title
	 * @return Page
	 */
	public function setTitle($title)
	{
		$this->oldTitle = $this->title;
		$this->title = $title;

		return $this;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
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
	 * Set navigationTitle
	 *
	 * @param  string $navigationTitle
	 * @return Page
	 */
	public function setNavigationTitle($navigationTitle)
	{
		$this->navigationTitle = $navigationTitle;

		return $this;
	}

	/**
	 * Get navigationTitle
	 *
	 * @return string
	 */
	public function getNavigationTitle()
	{
		return $this->navigationTitle;
	}

	/**
	 * Set active
	 *
	 * @param string status
	 * @return Page
	 */
	public function setStatus($active)
	{
		$this->status = $active;

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
		if(!$this->activeFrom) return new \DateTime();

		return $this->activeFrom;
	}

	/**
	 * Set activeTill
	 *
	 * @param  date $activeTill
	 * @return Page
	 */
	public function setActiveTill($activeTill)
	{
		$this->activeTill = $activeTill;

		return $this;
	}

	/**
	 * Get activeTill
	 *
	 * @return date
	 */
	public function getActiveTill()
	{
		return $this->activeTill;
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

	public function orderLayoutBlocks()
	{
		$layoutBlocks = $this->layoutBlock->toArray();

		usort($layoutBlocks, array(__CLASS__, 'compareSortOrder'));
		$this->layoutBlock = new ArrayCollection();
		foreach ($layoutBlocks as $layoutBlock) {
			$this->layoutBlock->add($layoutBlock);
		}
	}

	private function compareSortOrder($a, $b)
	{
		return strcmp($a->getSortOrder(), $b->getSortOrder());
	}

	/**
	 * Get menuItem
	 *
	 * @param  null                                                                                 $zone
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
	 * Add tags
	 *
	 * @param  Tag  $tags
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
		if (!$this->title) {
			if ($this->oldTitle) {
				return $this->oldTitle;
			}

			return '-------';
		}

		return $this->title;
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
	 * @return Page
	 */
	public function getOriginals()
	{
		return $this->originals;
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

		foreach ($translations as $translation) {
			$translation->setPage($this);
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

	public function getAdminTitle()
	{
		$countParents = count($this->getParents());
		$prefix = '';
		for ($i = 0; $i < $countParents; $i++) {
			$prefix .= '- ';
		}

		return $prefix . '' . $this->getTitle();
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
	public function getFullPath()
	{
		return $this->contentRoute->getPath();
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
	 * @return ArrayCollection
	 */
	public function getAllTranslations()
	{

		$translationsArray = array();

		$this->getRecuriveTranslations($translationsArray);

		$allTranslations = new ArrayCollection($translationsArray);

		return $allTranslations;
	}

	/**
	 * Recursively search for all possible translations of this page, either originals
	 * of this page, translations of this page or translations of the original of this page.
	 *
	 * @param  type $translationsArray
	 * @return type
	 */
	public function getRecuriveTranslations(&$translationsArray)
	{
		// find all possible translations
		if ($this->getTranslations()->count()) {
			foreach ($this->getTranslations() as $translation) {
				// if we already meet you stop and go on with the next
				$translationsArray[$translation->getLocale()] = $translation;
				$translation->getRecuriveTranslations($translationsArray);
			}

		}

		// find all possible originals
		if ($this->getOriginals()->count()) {
			foreach ($this->getOriginals() as $translation) {
				// if we already meet you stop and go on with the next
				if (array_key_exists($translation->getLocale(), $translationsArray)) return;
				$translationsArray[$translation->getLocale()] = $translation;
				$translation->getRecuriveTranslations($translationsArray);
			}
		}
	}

	/**
	 * @return array
	 */
	public static function getStatusList()
	{
		return array(
			self::STATUS_DRAFT => 'status_draft',
			self::STATUS_REVIEW => 'status_review',
			self::STATUS_PUBLISHED => 'status_published'
		);
	}

	public static function getVisibilityList()
	{
		return array(
			self::VISIBILITY_PUBLIC => 'visibility_public',
			self::VISIBILITY_PROTECTED => 'visibility_protected'
		);
	}
}
