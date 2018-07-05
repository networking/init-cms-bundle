<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\Iterator\RecursiveItemIterator;
use Knp\Menu\Matcher\MatcherInterface;
use Knp\Menu\Matcher\Voter\UriVoter;
use Networking\InitCmsBundle\Model\MenuItemManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuItem as Menu;
use Networking\InitCmsBundle\Entity\MenuItem;
use Networking\InitCmsBundle\Entity\BasePage as Page;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Knp\Menu\Matcher\Matcher;
use Symfony\Component\HttpFoundation\RequestStack;
/**
 * Class MenuBuilder
 * @package Networking\InitCmsBundle\Component\Menu
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MenuBuilder
{

    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var bool
     */
    protected $isLoggedIn;

    /**
     * @var MenuItemManagerInterface
     */
    protected $menuManager;

    /**
     * @var TranslatorInterface
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
    protected $menuIterators = [];


    /**
     * MenuBuilder constructor.
     * @param FactoryInterface $factory
     * @param TokenStorageInterface $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param RequestStack $requestStack
     * @param RouterInterface $router
     * @param MenuItemManagerInterface $menuManager
     * @param TranslatorInterface $translator
     * @param Matcher $matcher
     */
    public function __construct(
        FactoryInterface $factory,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        RequestStack $requestStack,
        RouterInterface $router,
        MenuItemManagerInterface $menuManager,
        TranslatorInterface $translator,
        MatcherInterface $matcher
    ) {

        $this->factory = $factory;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
        $this->request = $requestStack->getCurrentRequest();
        $this->menuManager = $menuManager;
        $this->translator = $translator;
        $this->matcher = $matcher;

        $this->setLoggedIn();
        $this->setViewStatus();
        $this->setCurrentPath();
        $this->setCurrentUri();
    }

    /**
     *
     */
    public function setLoggedIn()
    {
        if ($this->tokenStorage->getToken() && ($this->authorizationChecker->isGranted(
                    'IS_AUTHENTICATED_FULLY'
                ) || $this->authorizationChecker->isGranted(
                    'IS_AUTHENTICATED_REMEMBERED'
                ))
        ) {
            $this->isLoggedIn = true;
        }
    }

    /**
     *
     */
    public function setViewStatus()
    {
        $this->viewStatus = $this->request->getSession()->get('_viewStatus')
            ? $this->request->getSession()->get('_viewStatus')
            : Page::STATUS_PUBLISHED;
    }

    /**
     *
     */
    public function setCurrentPath(){
        $this->currentPath = substr($this->request->getPathInfo(), -1) != '/' ? $this->request->getPathInfo(
            ) . '/' : $this->request->getPathInfo();
    }

    /**
     *
     */
    public function setCurrentUri()
    {
        $this->currentUri = $this->request->getBaseUrl() . $this->currentPath;
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
            $menu->addChild('logout', ['route' => 'fos_user_security_logout']);
        } else {
            $menu->addChild('login', ['route' => 'fos_user_security_login']);
            $menu->addChild('register', ['route' => 'fos_user_registration_register']);
        }

        return $menu;
    }

    /**
     * Recursively get parents
     *
     * @param $menu - menu to look for the parent in
     * @param $childNode - menu node whose parent we are looking for
     * @return mixed
     */
    public function getParentMenu(Menu $menu, MenuItem $childNode)
    {
        $itemIterator = new RecursiveItemIterator($menu->getIterator());
        $iterator = new \RecursiveIteratorIterator($itemIterator, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $menuItem) {
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

        $options = [
            'uri' => $uri,
            'label' => $menuItem->getName(),
            'attributes' => [],
            'linkAttributes' => $menuItem->getLinkAttributes(),
            'childrenAttributes' => [],
            'labelAttributes' => [],
            'extras' => [],
            'display' => true,
            'displayChildren' => true,

        ];
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
            ['name' => $menuName, 'locale' => $this->request->getLocale()]
        );

        if (!$mainMenu) {
            return [];
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
            $menu = $this->getParentMenu($menu, $node);
        }

        if (is_object($menu)) {
            $knpMenuNode = $this->createFromMenuItem($node);
            if (!is_null($knpMenuNode)) {
                $menu->addChild($knpMenuNode);
                $knpMenuNode->setAttribute('class', $node->getLinkClass());
                $knpMenuNode->setExtra('translation_domain', false);
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
     * @param ItemInterface $menu
     */
    public function showOnlyCurrentChildren(ItemInterface $menu)
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
     * @param ItemInterface $menu
     * @param array $attr
     */
    public function setRecursiveChildrenAttribute(ItemInterface $menu, array $attr)
    {
        $this->setRecursiveAttribute($menu, $attr);
    }

    /**
     * Recursively set attributes on an item and its' children
     *
     * @param ItemInterface $menu
     * @param array $attr
     */
    public function setRecursiveAttribute(ItemInterface $menu, array $attr)
    {
        $itemIterator = new RecursiveItemIterator($menu->getIterator());

        $iterator = new \RecursiveIteratorIterator($itemIterator, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $menuItem) {
            /** @var ItemInterface $menuItem */
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
