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

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Networking\InitCmsBundle\Model\PageInterface;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity
 * @ORM\Table(name="cms_menu")
 * @ORM\Entity(repositoryClass="Networking\InitCmsBundle\Entity\MenuItemRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @author net working AG <info@networking.ch>
 */
class MenuItem implements MenuItemInterface, \IteratorAggregate
{

    /**
     * @var array $options
     */
    protected $options = array();


    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Networking\InitCmsBundle\Entity\Page", inversedBy="menuItem", cascade={"persist"})
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $page;


    /**
     * @ORM\Column(name="redirect_url", type="string", length=255, nullable=true)
     */
    protected $redirectUrl;

    /**
     * @ORM\Column(name="internal_url", type="string", length=255, nullable=true)
     */
    protected $internalUrl;

    /**
     * @ORM\Column(name="hidden", type="boolean", nullable=true)
     */
    protected $hidden;

    /**
     * @ORM\Column(name="link_target", type="string", length=255, nullable=true)
     */
    protected $linkTarget;

    /**
     * @ORM\Column(name="link_class", type="string", length=255, nullable=true)
     */
    protected $linkClass;

    /**
     * @ORM\Column(name="link_rel", type="string", length=255, nullable=true)
     */
    protected $linkRel;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    protected $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="MenuItem", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="MenuItem", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected $children;

    /**
     * @var bool
     * @ORM\Column(name="is_root", type="boolean")
     */
    protected $isRoot = false;

    /**
     * @var string $locale;
     *
     * @ORM\Column(name="locale")
     */
    protected $locale;

    /**
     * @var string $path
     */
    protected $path;


    /**
     * @var text $description
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string $visibility
     *
     * @ORM\Column(name="visibility", type="string", length=50)
     */
    protected $visibility = self::VISIBILITY_PUBLIC;

    /**
     *
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->name;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        if ($this->getParent()) {
            $this->setLocale();
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param PageInterface $page
     * @return $this
     */
    public function setPage(PageInterface $page = null)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return PageInterface
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param $redirectUrl
     * @return $this
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * @param $hidden
     * @return $this
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * @return mixed
     */
    public function isHidden()
    {
        return $this->getHidden();
    }

    /**
     * @param $route
     * @return $this
     */
    public function setInternalUrl($route)
    {
        $this->internalUrl = $route;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInternalUrl()
    {
        return $this->internalUrl;
    }

    /**
     * @param $name
     * @return MenuItem
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param MenuItemInterface $parent
     * @return $this
     */
    public function setParent(MenuItemInterface $parent = null)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param $lft
     * @return $this
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * @param $lvl
     * @return $this
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * @param $rgt
     * @return $this
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * @param $root
     * @return $this
     */
    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @return MenuItem
     */
    public function getMenu()
    {
        return $this->getRootParent($this);
    }

    /**
     * @param boolean $isRoot
     * @return $this
     */
    public function setIsRoot($isRoot)
    {
        $this->isRoot = $isRoot;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsRoot()
    {
        return $this->isRoot;
    }

    /**
     * @param  MenuItemInterface $menuItem
     * @return MenuItemInterface
     */
    public function getRootParent(MenuItemInterface $menuItem)
    {
        if (!$menuItem->getId()) {
            return false;
        }

        if ($parent = $menuItem->getParent()) {
            return $this->getRootParent($parent);
        }

        return $menuItem;
    }

    /**
     * @param  int           $level
     * @return bool|MenuItemInterface
     */
    public function getParentByLevel($level = 1)
    {
        if ($level === $this->getLvl()) {
            return $this;
        }

        if (!$this->getParent()) {
            return false;
        }

        if ($level === $this->getParent()->getLvl()) {
            return $this->getParent();
        }

        return $this->getParent()->getParentByLevel($level);
    }

    /**
     * @param  MenuItemInterface $menuItem
     * @return $this
     */
    public function setMenu(MenuItemInterface $menuItem = null)
    {
        if ($menuItem) {
            $this->setParent($menuItem);
        }

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getActiveChildren()
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

    /**
     * @param $status
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildrenByStatus($status)
    {
        $children = new ArrayCollection();
        foreach ($this->getChildren() as $child) {
            if ($status === Page::STATUS_PUBLISHED) {
                if ($child->getPage() && !$child->getPage()->getSnapshot()) {
                    continue;
                }
            }
            $children->add($child);
        }

        return $children;
    }


    /**
     * @param $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        if($this->path){
            return $this->path;
        }

        if (!$this->getPage()) {
            return;
        }

        return $this->getPage()->getContentRoute()->getPath();
    }

    /**
     * @return string
     */
    public function getRouteId()
    {
        if (!$this->getPage()) {
            return;
        }

        return $this->getPage()->getContentRoute()->getId();
    }

    /**
     * @param  null     $locale
     * @return $this
     */
    public function setLocale($locale = null)
    {
        if (is_null($locale)) {
            $locale = $this->getParent()->getLocale();
        }
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
     * @param \Networking\InitCmsBundle\Entity\text $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Implements IteratorAggregate
     */
    public function getIterator()
    {
        return $this->children->getIterator();
    }


    /**
     * @param $linkClass
     * @return $this
     */
    public function setLinkClass($linkClass)
    {
        $this->linkClass = $linkClass;

        return $this;
    }

    /**
     * @return String
     */
    public function getLinkClass()
    {
        return $this->linkClass;
    }

    /**
     * @param $linkRel
     * @return $this
     */
    public function setLinkRel($linkRel)
    {
        $this->linkRel = $linkRel;

        return $this;
    }

    /**
     * @return String
     */
    public function getLinkRel()
    {
        return $this->linkRel;
    }

    /**
     * @param $linkTarget
     * @return $this
     */
    public function setLinkTarget($linkTarget)
    {
        $this->linkTarget = $linkTarget;

        return $this;
    }

    /**
     * @return String
     */
    public function getLinkTarget()
    {
        return $this->linkTarget;
    }

    /**
     * @param string $visibility
     * @return $this
     * @throws \InvalidArgumentException
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
     * @return array
     */
    public function getLinkAttributes()
    {
        $linkAttributes = array();

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

    /**
     * @return int
     */
    public function hasChildren()
    {
        return $this->children->count();
    }

}

