<?php
/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Networking\InitCmsBundle\Model;

/**
 * Class PageInterface
 * @package Networking\InitCmsBundle\Model
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface PageInterface extends \Symfony\Cmf\Component\Routing\RouteReferrersReadInterface, \Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface

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
     * Set updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt();

    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * Set createdAt
     *
     * @param  \Datetime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get createdAt
     *
     * @return \Datetime
     */
    public function getCreatedAt();

    /**
     * Get updatedAt
     *
     * @return \Datetime
     */
    public function getUpdatedAt();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * Set pageName
     *
     * @param  string $title
     * @return $this
     */
    public function setPageName($title);

    /**
     * Get pageName
     *
     * @return string
     */
    public function getPageName();

    /**
     * Set metaTitle
     *
     * @param  string $title
     * @return $this
     */
    public function setMetaTitle($title);

    /**
     * Get metaTitle
     *
     * @return string
     */
    public function getMetaTitle();

    /**
     * @param $path
     */
    public function setPath($path);

    /**
     * @return string
     */
    public function getPath();

    /**
     * @return mixed
     */
    public function getLevel();

    /**
     * @param $metaKeyword
     */
    public function setMetaKeyword($metaKeyword);

    /**
     * @return string
     */
    public function getMetaKeyword();

    /**
     * @param $metaDescription
     */
    public function setMetaDescription($metaDescription);

    /**
     * @return string
     */
    public function getMetaDescription();


    /**
     * @param  PageInterface $parent
     * @return $this
     */
    public function setParent(PageInterface $parent = null);

    /**
     * @param $level
     * @return null
     */
    public function getParent($level = -1);

    /**
     * @param PageInterface $alias
     */
    public function setAlias(PageInterface $alias = null);

    /**
     * @return PageInterface
     */
    public function getAlias();

    /**
     * @param  array $parents
     * @return $this
     */
    public function setParents(array $parents);

    /**
     * @return array
     */
    public function getParents();

    /**
     * @param  PageInterface $children
     * @return $this
     */
    public function addChildren(PageInterface $children);

    /**
     * @return mixed
     */
    public function getChildren();

    /**
     * @param $children
     * @return $this
     */
    public function setChildren($children);

    /**
     * @param  array $children
     * @return array
     */
    public function getAllChildren(&$children = array());

    /**
     * @param $children
     * @return $this
     */
    public function setAllChildren($children);

    /**
     * Set active
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set page visibility
     *
     * @param string $visibility
     * @return $this
     */
    public function setVisibility($visibility);

    /**
     * Get page visibility
     *
     * @return string
     */
    public function getVisibility();

    /**
     *
     * @return bool
     */
    public function isDraft();

    /**
     *
     * @return bool
     */
    public function isReview();

    /**
     *
     * @return bool
     */
    public function isPublished();

    /**
     *
     * @return bool
     */
    public function isActive();


    /**
     * Set activeTo
     *
     * @param  $activeTo
     * @return $this
     */
    public function setActiveTo($activeTo);

    /**
     * Get activeTo
     *
     * @return string
     */
    public function getActiveTo();
    

    /**
     * Set activeFrom
     *
     * @param  $activeFrom
     * @return $this
     */
    public function setActiveFrom($activeFrom);

    /**
     * Get activeFrom
     *
     * @return string
     */
    public function getActiveFrom();

    /**
     * Add layout block
     *
     * @param  LayoutBlockInterface $layoutBlock
     * @return $this
     */
    public function addLayoutBlock(LayoutBlockInterface $layoutBlock);

    /**
     * remove content
     *
     * @param  LayoutBlockInterface $layoutBlock
     * @return $this
     */
    public function removeLayoutBlock(LayoutBlockInterface $layoutBlock);

    /**
     * Remove all layout blocks and replace with those in the
     * serialized page snapshot
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $publishedBlocks
     */
    public function resetLayoutBlock($publishedBlocks);

    /**
     * @param $layoutBlocks
     * @return $this
     */
    public function setLayoutBlock($layoutBlocks);

    /**
     *
     */
    public function orderLayoutBlocks();

    /**
     * Get menuItem
     *
     * @param  null $zone
     * @return \Doctrine\Common\Collections\ArrayCollection|\Doctrine\Common\Collections\Collection
     */
    public function getLayoutBlock($zone = null);

    /**
     * Add menuItem
     *
     * @param  MenuItemInterface $menuItem
     * @return $this
     */
    public function setMenuItem(MenuItemInterface $menuItem);

    /**
     * remove menuItem
     *
     * @param  MenuItemInterface $menuItem
     * @return $this
     */
    public function removeMenuItem(MenuItemInterface $menuItem);

    /**
     * Get menuItem
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMenuItem();

    /**
     * @param $rootId
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMenuItemByRoot($rootId);

    /**
     * @return string
     */
    public function __toString();

    /**
     * @param $isHome
     * @return $this
     */
    public function setIsHome($isHome);

    /**
     * @return bool
     */
    public function getIsHome();

    /**
     * @return bool
     */
    public function isHome();

    /**
     * @param  string $locale
     * @return $this
     */
    public function setLocale($locale);

    /**
     * @return string
     */
    public function getLocale();

    /**
     * @param  array $originals
     * @return $this
     */
    public function setOriginals(array $originals);

    /**
     * @param  PageInterface $page
     * @return $this
     */
    public function setOriginal(PageInterface $page);

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getOriginals();

    /**
     * @param PageInterface $page
     * @return bool
     */
    public function isDirectTranslation(PageInterface $page);

    /**
     * @param PageInterface $page
     * @return mixed
     */
    public function getDirectTranslationFor(PageInterface $page);

    /**
     * @param PageInterface $page
     * @return $this
     */
    public function addTranslation(PageInterface $page);

    /**
     * @param PageInterface $page
     * @return $this
     */
    public function removeTranslation(PageInterface $page);


    /**
     * @param  array $translations
     * @return $this
     */
    public function setTranslations(array $translations);

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTranslations();

    /**
     * @return array
     */
    public function getTranslatedLocales();

    /**
     * @return string
     */
    public function getAdminTitle();

    /**
     * @param string $url
     */
    public function setUrl($url);

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @param  ContentRouteInterface $contentRoute
     * @return $this
     */
    public function setContentRoute(ContentRouteInterface $contentRoute);

    /**
     * @return ContentRouteInterface
     */
    public function getContentRoute();


    /**
     * @param $snapshots
     * @return $this
     */
    public function setSnapshots($snapshots);

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection $snapshots
     */
    public function getSnapshots();

    /**
     * {@inheritDoc}
     */
    public function getRoute();

    /**
     * @param $template
     * @return $this
     */
    public function setTemplate($template);

    /**
     * @return string
     */
    public function getTemplate();


    /**
     * @param $templateName
     * @return $this
     */
    public function setTemplateName($templateName);

    /**
     * @return string
     */
    public function getTemplateName();

    /**
     * @return string
     */
    public function getFullPath();

    /**
     * @return string
     */
    public function getAliasFullPath();

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAllTranslations();

    /**
     * Recursively search for all possible translations of this page, either originals
     * of this page, translations of this page or translations of the original of this page.
     *
     * @param  array $translationsArray
     * @return array
     */
    public function getRecursiveTranslations(&$translationsArray);

    /**
     * @return array
     */
    public static function getStatusList();

    /**
     * @return array
     */
    public static function getVisibilityList();

    /**
     * @return boolean
     */
    public function hasPublishedVersion();

    /**
     * @return int
     */
    public function convertParentToInteger();

    /**
     * @return int
     */
    public function convertAliasToInteger();

    /**
     * @param $id
     * @return $this|null
     */
    public function convertIntegerToPage($id);

    /**
     * @return array
     */
    public function convertParentsToArray();

    /**
     * @return array
     */
    public function convertChildrenToIntegerArray();

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function prepareMenuItemsForSerialization();

    /**
     * @return array
     */
    public function convertTranslationsToIntegerArray();

    /**
     * @return array
     */
    public function convertOriginalsToIntegerArray();

    /**
     * @return string
     */
    public function getStatusLabel();
}