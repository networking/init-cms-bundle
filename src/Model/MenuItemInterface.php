<?php

declare(strict_types=1);

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
 * Class MenuItemInterface.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface MenuItemInterface
{
    public const PATH_SEPARATOR = '/';

    public const VISIBILITY_PUBLIC = 'public';

    public const VISIBILITY_PROTECTED = 'protected';

    /**
     * @return string
     */
    public function __toString();

    /**
     * @return int
     */
    public function getId();

    /**
     * @param PageInterface $page
     *
     * @return $this
     */
    public function setPage(PageInterface $page = null);

    /**
     * Get conversation.
     *
     * @return PageInterface
     */
    public function getPage();

    /**
     * @param $redirectUrl
     */
    public function setRedirectUrl($redirectUrl);

    /**
     * @return string
     */
    public function getRedirectUrl();

    /**
     * @param $hidden
     */
    public function setHidden($hidden);
    /**
     * @return bool
     */
    public function getHidden();

    /**
     * @return bool
     */
    public function isHidden();

    /**
     * @param $route
     */
    public function setInternalUrl($route);

    /**
     * @return string
     */
    public function getInternalUrl();

    /**
     * @param $name
     *
     * @return MenuItemInterface
     */
    public function setName($name);

    /**
     * Get name.
     *
     * @return string
     */
    public function getName();

    /**
     * @param MenuItemInterface $parent
     *
     * @return $this
     */
    public function setParent(MenuItemInterface $parent = null);

    /**
     * @return MenuItemInterface
     */
    public function getParent();

    /**
     * @param $lft
     *
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
     * @return int
     */
    public function getLvl();

    /**
     * @param $rgt
     *
     * @return $this
     */
    public function setRgt($rgt);

    /**
     * @return int
     */
    public function getRgt();

    /**
     * @param $root
     *
     * @return $this
     */
    public function setRoot($root);

    /**
     * @return int
     */
    public function getRoot();

    /**
     * @return $this
     */
    public function getMenu();

    /**
     * @param bool $isRoot
     */
    public function setIsRoot($isRoot);

    /**
     * @return bool
     */
    public function getIsRoot();

    /**
     * @param MenuItemInterface $menuItem
     *
     * @return MenuItemInterface
     */
    public function getRootParent(MenuItemInterface $menuItem);

    /**
     * @param int $level
     *
     * @return bool|MenuItem
     */
    public function getParentByLevel($level = 1);

    /**
     * @param MenuItemInterface $menuItem
     *
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
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildrenByStatus($status);

    /**
     * @param $path
     *
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
     * @param null $locale
     *
     * @return $this
     */
    public function setLocale($locale = null);

    /**
     * @return string
     */
    public function getLocale();

    /**
     * @param \Networking\InitCmsBundle\Entity\text $description
     *
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param $linkClass
     *
     * @return MenuItem
     */
    public function setLinkClass($linkClass);

    /**
     * @return string
     */
    public function getLinkClass();

    /**
     * @param $linkRel
     *
     * @return $this
     */
    public function setLinkRel($linkRel);

    /**
     * @return string
     */
    public function getLinkRel();

    /**
     * @param $linkTarget
     *
     * @return $this
     */
    public function setLinkTarget($linkTarget);

    /**
     * @return string
     */
    public function getLinkTarget();

    /**
     * Set page visibility.
     *
     * @param string $visibility
     *
     * @return $this
     */
    public function setVisibility($visibility);

    /**
     * Get page visibility.
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
