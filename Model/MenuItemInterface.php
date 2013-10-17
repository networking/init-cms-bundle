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
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface MenuItemInterface
{
    /**
     * @var string
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
     * @return string
     */
    public function __toString();

    /**
     * @param  PageInterface $page
     * @return $this
     */
    public function setPage(PageInterface $page = null);

    /**
     * Get conversation
     *
     * @return PageInterface
     */
    public function getPage();

    /**
     * @param $redirectUrl
     */
    public function setRedirectUrl($redirectUrl);

    /**
     * @return mixed
     */
    public function getRedirectUrl();

    /**
     * @param $hidden
     */
    public function setHidden($hidden);
    /**
     * @return mixed
     */
    public function getHidden();

    /**
     * @return mixed
     */
    public function isHidden();

    /**
     * @param $route
     */
    public function setInternalUrl($route);

    /**
     * @return mixed
     */
    public function getInternalUrl();

    /**
     * @param $name
     * @return MenuItemInterface
     */
    public function setName($name);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * @param MenuItemInterface $parent
     * @return $this
     */
    public function setParent(MenuItemInterface $parent = null);

    /**
     * @return mixed
     */
    public function getParent();

    /**
     * @param $lft
     * @return $this
     */
    public function setLft($lft);

    /**
     * @return mixed
     */
    public function getLft();

    /**
     * @param $lvl
     */
    public function setLvl($lvl);

    /**
     * @return mixed
     */
    public function getLvl();

    /**
     * @param $rgt
     * @return $this
     */
    public function setRgt($rgt);

    /**
     * @return mixed
     */
    public function getRgt();

    /**
     * @param $root
     * @return $this
     */
    public function setRoot($root);

    /**
     * @return mixed
     */
    public function getRoot();

    /**
     * @return $this
     */
    public function getMenu();

    /**
     * @param boolean $isRoot
     */
    public function setIsRoot($isRoot);

    /**
     * @return boolean
     */
    public function getIsRoot();

    /**
     * @param  MenuItemInterface $menuItem
     * @return MenuItemInterface
     */
    public function getRootParent(MenuItemInterface $menuItem);

    /**
     * @param  int $level
     * @return bool|MenuItem
     */
    public function getParentByLevel($level = 1);

    /**
     * @param  MenuItemInterface $menuItem
     * @return $this
     */
    public function setMenu(MenuItemInterface $menuItem = null);

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildren();

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getActiveChildren();

    /**
     * @param $status
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildrenByStatus($status);


    /**
     * @param $path
     * @return $this
     */
    public function setPath($path);

    /**
     * @return string
     */
    public function getPath();

    /**
     * @return string
     */
    public function getRouteId();

    /**
     * @param  null $locale
     * @return $this
     */
    public function setLocale($locale = null);

    /**
     * @return string
     */
    public function getLocale();

    /**
     * @param \Networking\InitCmsBundle\Entity\text $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getDescription();


    /**
     * @param $linkClass
     * @return MenuItem
     */
    public function setLinkClass($linkClass);

    /**
     * @return String
     */
    public function getLinkClass();

    /**
     * @param $linkRel
     * @return $this
     */
    public function setLinkRel($linkRel);

    /**
     * @return String
     */
    public function getLinkRel();

    /**
     * @param $linkTarget
     * @return $this
     */
    public function setLinkTarget($linkTarget);

    /**
     * @return String
     */
    public function getLinkTarget();

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
     * @return array
     */
    public static function getVisibilityList();

    /**
     * @return array
     */
    public function getLinkAttributes();

    /**
     * @return int
     */
    public function hasChildren();

}