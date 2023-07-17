<?php

declare(strict_types=1);

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Iterator\RecursiveItemIterator;
use Knp\Menu\Matcher\MatcherInterface;
use Knp\Menu\Matcher\Matcher;
use Knp\Menu\MenuItem as Menu;
use Networking\InitCmsBundle\Model\ContentRouteManager;
use Networking\InitCmsBundle\Model\MenuItemManagerInterface;
use Networking\InitCmsBundle\Model\MenuItem;
use Networking\InitCmsBundle\Model\Page;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class MenuBuilder.
 *
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
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var Request
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
     *
     * @param FactoryInterface $factory
     * @param TokenStorageInterface $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param RequestStack $requestStack
     * @param RouterInterface $router
     * @param MenuItemManagerInterface $menuManager
     * @param TranslatorInterface $translator
     * @param MatcherInterface $matcher
     * @param bool $allowLocaleCookie
     */
    public function __construct(
        FactoryInterface $factory,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        RequestStack $requestStack,
        RouterInterface $router,
        MenuItemManagerInterface $menuManager,
        TranslatorInterface $translator,
        MatcherInterface $matcher,
        protected $allowLocaleCookie = true
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

    public function setViewStatus()
    {
        $this->viewStatus = $this->request->getSession()->get('_viewStatus') ?: Page::STATUS_PUBLISHED;
    }

    public function setCurrentPath()
    {
        $this->currentPath = !str_ends_with($this->request->getPathInfo(), '/') ? $this->request->getPathInfo(
            ).'/' : $this->request->getPathInfo();
    }

    public function setCurrentUri()
    {
        $this->currentUri = $this->request->getBaseUrl().$this->currentPath;
    }

    /**
     * Creates the login and change language navigation for the right side of the top frontend navigation.
     *
     * @param string $classes
     *
     * @return \Knp\Menu\ItemInterface
     * @internal param $languages
     *
     * @internal param \Symfony\Component\HttpFoundation\Request $request
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
     * Recursively get parents.
     *
     * @param $menu - menu to look for the parent in
     * @param $childNode - menu node whose parent we are looking for
     *
     * @return mixed
     */
    public function getParentMenu(Menu $menu, MenuItem $childNode)
    {
        $itemIterator = new RecursiveItemIterator($menu->getIterator());
        $iterator = new \RecursiveIteratorIterator($itemIterator, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $menuItem) {
            $parentId = $childNode->getParent()->getId();
            if ($menuItem->getName() !== sprintf('menu_%s', $parentId)) {
                continue;
            }

            return $menuItem;
        }

        return false;
    }

    /**
     * Create an new node using the ContentRoute object to generate the uri.
     *
     * @param \Networking\InitCmsBundle\Entity\MenuItem $menuItem
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createFromMenuItem(MenuItem $menuItem)
    {
        if ($path = $menuItem->getPath()) {
            $uri = $this->request->getBaseUrl().$path;
        } elseif ($menuItem->getRedirectUrl()) {
            $uri = $menuItem->getRedirectUrl();
        } elseif ($menuItem->getInternalUrl()) {
            $uri = $this->request->getBaseUrl().$menuItem->getInternalUrl();
        } elseif ($contentRoute = $menuItem->getContentRoute()) {
            $route = ContentRouteManager::generateRoute($contentRoute, $contentRoute->getPath(), '');
            $uri = $this->request->getBaseUrl().$route->getPath();
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
        $item = $this->factory->createItem('menu_'.$menuItem->getId(), $options);

        if ($menuItem->isHidden()) {
            $item->setDisplay(false);
        }

        return $item;
    }

    /**
     * Retrieve the full menu tree.
     *
     * @param string $menuName
     *
     * @return MenuItem[]|bool
     */
    public function getFullMenu($menuName)
    {
        if (is_array($menuName)) {
            $menuName = reset($menuName);
        }

        if (array_key_exists($menuName, $this->menuIterators) && (is_countable($this->menuIterators[$menuName]) ? count($this->menuIterators[$menuName]) : 0) > 0) {
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
     * Retrieves the sub menu array based on the current url.
     *
     * @param string $menuName
     * @param int $level
     *
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
     * Creates a full menu based on the starting point given.
     *
     * @param array $menuIterator
     * @param int $startDepth
     * @return ItemInterface
     */
    public function createMenu(ItemInterface $item, $menuIterator, $startDepth)
    {
        foreach ($menuIterator as $childNode) {
            $this->addNodeToMenu($item, $childNode, $startDepth);
        }

        return $item;
    }

    /**
     * Add a menu item node at the correct place in the menu.
     *
     * @param ItemInterface $item
     * @param MenuItem $node
     * @param $startDepth
     *
     * @return bool|\Knp\Menu\ItemInterface
     */
    public function addNodeToMenu(ItemInterface $item, MenuItem $node, $startDepth)
    {
        if ($node->getLvl() < $startDepth) {
            return false;
        }

        if ($node->getLvl() > $startDepth) {
            $item = $this->getParentMenu($item, $node);
        }

        if (is_object($item)) {
            $knpMenuNode = $this->createFromMenuItem($node);
            if (!is_null($knpMenuNode)) {
                $item->addChild($knpMenuNode);
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
     * is current or the parent is a current ancestor.
     */
    public function showOnlyCurrentChildren(ItemInterface $item)
    {
        $itemIterator = new RecursiveItemIterator($item->getIterator());

        $iterator = new \RecursiveIteratorIterator($itemIterator, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $menuItem) {
            /** @var \Knp\Menu\MenuItem $menuItem */
            if (!$this->matcher->isCurrent($menuItem) && !$this->matcher->isAncestor($menuItem)) {
                $menuItem->setDisplayChildren(false);
            }
        }
    }

    /**
     * Recursively set attributes on all children of a given menu.
     *
     * @deprecated please use setRecursiveAttribute
     * @alias setRecursiveAttribute
     *
     */
    public function setRecursiveChildrenAttribute(ItemInterface $item, array $attr)
    {
        $this->setRecursiveAttribute($item, $attr);
    }

    /**
     * Recursively set attributes on an item and its' children.
     */
    public function setRecursiveAttribute(ItemInterface $item, array $attr)
    {
        $itemIterator = new RecursiveItemIterator($item->getIterator());

        $iterator = new \RecursiveIteratorIterator($itemIterator, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $menuItem) {
            /* @var ItemInterface $menuItem */
            $menuItem->setChildrenAttributes($attr);
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
