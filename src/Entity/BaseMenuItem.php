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
use Doctrine\ORM\Mapping as ORM;
use Networking\InitCmsBundle\Model\ContentRouteInterface;
use Networking\InitCmsBundle\Model\MenuItemInterface;
use Networking\InitCmsBundle\Entity\BasePage;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Entity\Text;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\ORM\MappedSuperclass()]
class BaseMenuItem implements MenuItemInterface, \IteratorAggregate, \Stringable
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', length: 255)]
    protected $name;

    /**
     * @var PageInterface
     */
    protected $page;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $redirectUrl;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $internalUrl;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $hidden;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $linkTarget;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $linkClass;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $linkRel;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', name: 'lft')]
    #[Gedmo\TreeLeft()]
    protected $lft;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', name: 'lvl')]
    #[Gedmo\TreeLevel()]
    protected $lvl;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', name: 'rgt')]
    #[Gedmo\TreeRight()]
    protected $rgt;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', name: 'root', nullable: true)]
    #[Gedmo\TreeRoot()]
    protected $root;

    /**
     * @var MenuItemInterface
     */
    protected $parent;

    /**
     * @var ArrayCollection
     */
    protected $children;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $isRoot = false;

    /**
     * @var string ;
     */
    #[ORM\Column(type: 'string', length: 6)]
    protected $locale;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var text
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $description;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 50)]
    protected $visibility = self::VISIBILITY_PUBLIC;

    protected $wasValidated = false;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->name;
    }

    #[ORM\PrePersist]
    public function prePersist()
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
     *
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
     *
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
     *
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
     *
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
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param MenuItemInterface $parent
     *
     * @return $this
     */
    public function setParent(MenuItemInterface $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return MenuItemInterface
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param $lft
     *
     * @return $this
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * @return int
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * @param $lvl
     *
     * @return $this
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * @return int
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * @param $rgt
     *
     * @return $this
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * @return int
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * @param $root
     *
     * @return $this
     */
    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * @return int
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @return MenuItemInterface
     */
    public function getMenu()
    {
        return $this->getRootParent($this);
    }

    /**
     * @param bool $isRoot
     *
     * @return $this
     */
    public function setIsRoot($isRoot)
    {
        $this->isRoot = $isRoot;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsRoot()
    {
        return $this->isRoot;
    }

    /**
     * @param MenuItemInterface $menuItem
     *
     * @return bool|MenuItemInterface
     */
    public function getRootParent(MenuItemInterface $menuItem)
    {

        if ($parent = $menuItem->getParent()) {
            return $this->getRootParent($parent);
        }

        return $menuItem;
    }

    /**
     * @param int $level
     *
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
     * @param MenuItemInterface $menuItem
     *
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
     *
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
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return bool|string
     */
    public function getPath()
    {
        if ($this->path) {
            return $this->path;
        }

        if (!$this->getPage()) {
            return false;
        }

        return $this->getPage()->getContentRoute()->getPath();
    }

    /**
     * @return bool|int|string
     */
    public function getRouteId()
    {
        if (!$this->getPage()) {
            return false;
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

    /**
     * @param null $locale
     *
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
     *
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
     * Implements IteratorAggregate.
     */
    public function getIterator(): \Traversable
    {
        return $this->children->getIterator();
    }

    /**
     * @param $linkClass
     *
     * @return $this
     */
    public function setLinkClass($linkClass)
    {
        $this->linkClass = $linkClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getLinkClass()
    {
        return $this->linkClass;
    }

    /**
     * @param $linkRel
     *
     * @return $this
     */
    public function setLinkRel($linkRel)
    {
        $this->linkRel = $linkRel;

        return $this;
    }

    /**
     * @return string
     */
    public function getLinkRel()
    {
        return $this->linkRel;
    }

    /**
     * @param $linkTarget
     *
     * @return $this
     */
    public function setLinkTarget($linkTarget)
    {
        $this->linkTarget = $linkTarget;

        return $this;
    }

    /**
     * @return string
     */
    public function getLinkTarget()
    {
        return $this->linkTarget;
    }

    /**
     * @param string $visibility
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setVisibility($visibility)
    {
        if (!in_array($visibility, [self::VISIBILITY_PROTECTED, self::VISIBILITY_PUBLIC])) {
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
     * @return array
     */
    public function getLinkAttributes()
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

    /**
     * @return int
     */
    public function hasChildren()
    {
        return $this->children->count();
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function hasChild($id)
    {
        foreach ($this->getChildren() as $child) {
            if ($child->getId() == $id) {
                return true;
            }

            return $child->hasChild($id);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasPage()
    {
        return !is_null($this->page);
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, $payload){

        if($this->wasValidated){
            return;
        }
        if($this->getIsRoot() ){
            return;
        }
        if(!$this->getRedirectUrl() && !$this->getPage() && !$this->getInternalUrl()){
            $context->buildViolation('menu.page_or_url.required')
                ->atPath('page')
                ->addViolation();
        }
        $this->wasValidated = true;
    }
}