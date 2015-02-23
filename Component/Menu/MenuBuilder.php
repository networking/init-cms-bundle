<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Component\Menu;

use Knp\Menu\Iterator\RecursiveItemIterator;
use Knp\Menu\Matcher\Voter\UriVoter;
use Networking\InitCmsBundle\Model\MenuItemManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Mopa\Bundle\BootstrapBundle\Navbar\AbstractNavbarMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuItem as Menu;
use Networking\InitCmsBundle\Entity\MenuItem;
use Networking\InitCmsBundle\Entity\BasePage as Page;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\Translator;
use Knp\Menu\Matcher\Matcher;
/**
 * Class MenuBuilder
 * @package Networking\InitCmsBundle\Component\Menu
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MenuBuilder extends AbstractNavbarMenuBuilder
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $securityContext;
    /**
     * @var bool
     */
    protected $isLoggedIn;

    /**
     * @var MenuItemManagerInterface
     */
    protected $menuManager;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var RouterInterface $router
     */
    protected $router;

    /**
     * @var Request $request
     */
    protected $request;

    /**
     * @var string
     */
    protected $viewStatus;

    /**
     * @var string
     */
    protected $currentUri;

    /**
     * @var string
     */
    protected $currentPath;

    /**
     * @var Matcher
     */
    protected $matcher;

    /**
     * @var array
     */
    protected $menuIterators = array();


    /**
     * @param FactoryInterface $factory
     * @param SecurityContextInterface $securityContext
     * @param Request $request
     * @param RouterInterface $router
     * @param MenuItemManagerInterface $menuManager
     * @param Translator $translator
     * @param Matcher $matcher
     */
    public function __construct(
        FactoryInterface $factory,
        SecurityContextInterface $securityContext,
        Request $request,
        RouterInterface $router,
        MenuItemManagerInterface $menuManager,
        Translator $translator,
        Matcher $matcher
    ) {

        $this->securityContext = $securityContext;

        if ($this->securityContext->getToken() && ($this->securityContext->isGranted(
                    'IS_AUTHENTICATED_FULLY'
                ) || $this->securityContext->isGranted(
                    'IS_AUTHENTICATED_REMEMBERED'
                ))
        ) {
            $this->isLoggedIn = true;
        }
        $this->factory = $factory;
        $this->router = $router;
        $this->request = $request;
        $this->menuManager = $menuManager;
        $this->translator = $translator;
        $this->matcher = $matcher;

        $this->viewStatus = $request->getSession()->get('_viewStatus')
            ? $request->getSession()->get('_viewStatus')
            : Page::STATUS_PUBLISHED;


        $this->currentPath = substr($request->getPathInfo(), -1) != '/' ? $request->getPathInfo(
            ) . '/' : $request->getPathInfo();
        $this->currentUri = $request->getBaseUrl() . $this->currentPath;


        $voter = new UriVoter($request->getBaseUrl() .$request->getPathInfo());
        $this->matcher->addVoter($voter);

        $voter = new UriVoter($this->currentUri);
        $this->matcher->addVoter($voter);
    }


    /**
     * Creates the login and change language navigation for the right side of the top frontend navigation
     *
     * @param string $classes
     * @internal param \Symfony\Component\HttpFoundation\Request $request
     * @internal param $languages
     * @return \Knp\Menu\ItemInterface
     */
    public function createLoginMenu($classes = 'nav pull-right')
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', $classes);

        if ($this->isLoggedIn) {
            $menu->addChild($this->translator->trans('logout'), array('route' => 'fos_user_security_logout'));
        } else {
            $menu->addChild($this->translator->trans('login'), array('route' => 'fos_user_security_login'));
            $menu->addChild(
                $this->translator->trans('register'),
                array('route' => 'fos_user_registration_register')
            );
        }


        return $menu;
    }

    /**
     * Recursively get parents
     *
     * @param $menu - menu to look for the parent in
     * @param $childNode - menu node whose parent we are looking for
     * @param $startDepth - starting depth of the first menu level
     * @return mixed
     */
    public function getParentMenu(Menu $menu, MenuItem $childNode, $startDepth)
    {
        $itemIterator = new RecursiveItemIterator($menu->getIterator());
        $iterator = new \RecursiveIteratorIterator($itemIterator, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $menuItem) {
            /** @var \Knp\Menu\MenuItem $menuItem */
            $parentId = $childNode->getParent()->getId();
            if($menuItem->getName() != $parentId){
                continue;
            }

            return $menuItem;
        }

        return false;
    }

    /**
     * Create an new node using the ContentRoute object to generate the uri
     *
     * @param  \Networking\InitCmsBundle\Entity\MenuItem $menuItem
     * @return \Knp\Menu\ItemInterface
     */
    public function createFromMenuItem(MenuItem $menuItem)
    {
        if ($menuItem->getPath()) {
            $uri = $this->request->getBaseUrl() . $menuItem->getPath();
        } elseif ($menuItem->getRedirectUrl()) {
            $uri = $menuItem->getRedirectUrl();
        } elseif ($menuItem->getInternalUrl()) {
            $uri = $this->request->getBaseUrl() . $menuItem->getInternalUrl();
        } else {
            $uri = '#';
        }

        $options = array(
            'uri' => $uri,
            'label' => $menuItem->getName(),
            'attributes' => array(),
            'linkAttributes' => $menuItem->getLinkAttributes(),
            'childrenAttributes' => array(),
            'labelAttributes' => array(),
            'extras' => array(),
            'display' => true,
            'displayChildren' => true,

        );
        $item = $this->factory->createItem($menuItem->getId(), $options);

        if ($menuItem->isHidden()) {
            $item->setDisplay(false);
        }

        return $item;
    }


    /**
     * Retrieve the full menu tree
     *
     * @param string $menuName
     * @return array|bool
     */
    public function getFullMenu($menuName)
    {

        if(is_array($menuName)){
            $menuName = reset($menuName);
        }

        if(array_key_exists($menuName, $this->menuIterators) && count($this->menuIterators[$menuName]) > 0){
            return $this->menuIterators[$menuName];
        }

        /** @var $mainMenu Menu */
        $mainMenu = $this->menuManager->findOneBy(
            array('name' => $menuName, 'locale' => $this->request->getLocale())
        );

        if (!$mainMenu) {
            return array();
        }

        $this->menuIterators[$menuName] = $this->menuManager->getChildrenByStatus(
            $mainMenu,
            false,
            null,
            'ASC',
            false,
            $this->viewStatus
        );


        return $this->menuIterators[$menuName];

    }

    /**
     * Retrieves the sub menu array based on the current url
     *
     * @param string $menuName
     * @param int $level
     * @return array|bool
     */
    public function getSubMenu($menuName, $level = 1)
    {
        $currentParent = false;

        $mainMenuIterator = $this->getFullMenu($menuName);

        if (!$mainMenuIterator) {
            return false;
        }

        foreach ($mainMenuIterator as $menuItem) {

            if ($this->currentPath === $menuItem->getPath()
                || $this->currentPath === $menuItem->getInternalUrl()
            ) {
                $currentParent = $menuItem->getParentByLevel($level);
            }
        }

        if (!$currentParent) {
            return false;
        }

        $menuIterator = $this->menuManager->getChildrenByStatus(
            $currentParent,
            false,
            null,
            'ASC',
            false,
            $this->viewStatus
        );

        return $menuIterator;
    }


    /**
     * Creates a full menu based on the starting point given
     *
     * @param Menu $menu
     * @param array $menuIterator
     * @param int $startDepth
     * @return Menu
     */
    public function createMenu(Menu $menu, $menuIterator, $startDepth)
    {
        foreach ($menuIterator as $childNode) {
            $this->addNodeToMenu($menu, $childNode, $startDepth);
        }

        return $menu;
    }

    /**
     * Add a menu item node at the correct place in the menu
     *
     * @param Menu $menu
     * @param MenuItem $node
     * @param $startDepth
     * @return bool|\Knp\Menu\ItemInterface
     */
    public function addNodeToMenu(Menu $menu, MenuItem $node, $startDepth)
    {
        if ($node->getLvl() < $startDepth) {
            return false;
        }

        if ($node->getLvl() > $startDepth) {
            $menu = $this->getParentMenu($menu, $node, $startDepth);
        }

        if (is_object($menu)) {
            $knpMenuNode = $this->createFromMenuItem($node);
            if (!is_null($knpMenuNode)) {
                $menu->addChild($knpMenuNode);
                $knpMenuNode->setAttribute('class', $node->getLinkClass());
                if ($node->getVisibility() != MenuItem::VISIBILITY_PUBLIC && !$this->isLoggedIn) {
                    $knpMenuNode->setDisplay(false);
                }

                return $knpMenuNode;
            }
        }

        return false;

    }

    /**
     * Set the children menu item nodes to be shown only if the node
     * is current or the parent is a current ancestor
     *
     * @param \Knp\Menu\MenuItem $menu
     */
    public function showOnlyCurrentChildren(\Knp\Menu\MenuItem $menu)
    {
        $itemIterator = new RecursiveItemIterator($menu->getIterator());

        $iterator = new \RecursiveIteratorIterator($itemIterator, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $menuItem) {
            /** @var \Knp\Menu\MenuItem $menuItem */
            if (!$this->matcher->isCurrent($menuItem) && !$this->matcher->isAncestor($menuItem)) {
                $menuItem->setDisplayChildren(false);
            }
        }
    }

    /**
     * Recursively set attributes on all children of a given menu
     *
     * @deprecated please use setRecursiveAttribute
     * @alias setRecursiveAttribute
     * @param Menu $menu
     * @param array $attr
     */
    public function setRecursiveChildrenAttribute(\Knp\Menu\MenuItem $menu, array $attr)
    {
        $this->setRecursiveAttribute($menu, $attr);
    }

    /**
     * Recursively set attributes on an item and its' children
     *
     * @param Menu $menu
     * @param array $attr
     */
    public function setRecursiveAttribute(\Knp\Menu\MenuItem $menu, array $attr)
    {
        $itemIterator = new RecursiveItemIterator($menu->getIterator());

        $iterator = new \RecursiveIteratorIterator($itemIterator, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $menuItem) {
            /** @var \Knp\Menu\MenuItem $menuItem */
            $menuItem->setChildrenAttributes($attr);;
        }
    }

    /**
     * @return array
     */
    public function getMenuIterators()
    {
        return $this->menuIterators;
    }

    /**
     * @param $menuName
     * @param $iterator
     */
    public function addMenuIterator($menuName, $iterator)
    {
        $this->menuIterators[$menuName] = $iterator;
    }
}
