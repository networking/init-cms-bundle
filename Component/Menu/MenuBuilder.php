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

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Security\Core\SecurityContextInterface,
    Symfony\Component\DependencyInjection\Container,
    Mopa\Bundle\BootstrapBundle\Navbar\AbstractNavbarMenuBuilder,
    Knp\Menu\FactoryInterface,
    Knp\Menu\MenuItem as Menu,
    Networking\InitCmsBundle\Entity\MenuItem,
    Networking\InitCmsBundle\Entity\Page,
    Networking\InitCmsBundle\Component\Routing\CMSRoute;

/**
 * @author net working AG <info@networking.ch>
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
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $serviceContainer;
    /**
     * @var object
     */
    protected $router;

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
     * @param \Knp\Menu\FactoryInterface $factory
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     * @param \Symfony\Component\DependencyInjection\Container $serviceContainer
     */
    public function __construct(
        FactoryInterface $factory,
        SecurityContextInterface $securityContext,
        Container $serviceContainer
    )
    {

        parent::__construct($factory);
        $this->securityContext = $securityContext;

        if ($this->securityContext->getToken() && ($this->securityContext->isGranted('IS_AUTHENTICATED_FULLY') || $this->securityContext->isGranted(
                'IS_AUTHENTICATED_REMEMBERED'
            ))
        ) {
            $this->isLoggedIn = true;
        }

        $this->serviceContainer = $serviceContainer;
        $this->router = $this->serviceContainer->get('router');

        $this->viewStatus = $serviceContainer->get('session')->get('_viewStatus')
            ? $serviceContainer->get('session')->get('_viewStatus')
            : Page::STATUS_PUBLISHED;

        /** @var Request $request */
        $request = $this->serviceContainer->get('request');

        $this->currentPath = substr($request->getPathInfo(), -1) != '/' ? $request->getPathInfo() . '/' : $request->getPathInfo();
        $this->currentUri = $request->getBaseUrl() . $this->currentPath;
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
            $menu->addChild($this->get('translator')->trans('logout'), array('route' => 'fos_user_security_logout'));
        } else {
            $menu->addChild($this->get('translator')->trans('login'), array('route' => 'fos_user_security_login'));
            $menu->addChild(
                $this->get('translator')->trans('register'),
                array('route' => 'fos_user_registration_register')
            );
        }


        return $menu;
    }

    /**
     * Recursively get parents
     *
     * @param $menu
     * @param $childNode
     * @param $startDepth starting depth of the first menu level
     * @return mixed
     */
    public function getParentMenu($menu, $childNode, $startDepth)
    {
        $startDepth = $childNode->getLvl() - $startDepth;
        if ($startDepth == 0) {
            return $menu;
        }

        if ($menu->getName() ==
            $childNode->getParent()->getName()
        ) {
            return $menu;
        }

        $parentLevel = $childNode->getLvl() - $startDepth;

        $tmpMenu = $menu->getChild($childNode->getParentByLevel($parentLevel)->getName());

        return $this->getParentMenu($tmpMenu, $childNode, $startDepth);
    }


    /**
     * Recursively get parents
     *
     * @param Menu $menu
     * @param MenuItem $childNode
     * @param $depth
     * @return mixed
     * @deprecated please use getParentMenu
     */
    public function getMenuParentItem(Menu $menu, MenuItem $childNode, $depth)
    {
        if ($depth == 0) {
            return $menu;
        }

        if ($menu->getName() ==
            $childNode->getParent()->getName()
        ) {
            return $menu;
        }

        $parentLevel = $childNode->getLvl() - $depth;

        $tmpMenu = $menu->getChild($childNode->getParentByLevel($parentLevel)->getName());
        --$depth;

        return $this->getMenuParentItem($tmpMenu, $childNode, $depth);
    }


    /**
     * Create an new node using the ContentRoute object to generate the uri
     *
     * @param  \Networking\InitCmsBundle\Entity\MenuItem $menuItem
     * @param bool $uri
     * @return \Knp\Menu\ItemInterface
     */
    public function createFromMenuItem(MenuItem $menuItem)
    {
        if ($menuItem->getPath()) {
            $uri = $this->serviceContainer->get('request')->getBaseUrl() . $menuItem->getPath();
        } elseif ($menuItem->getRedirectUrl()) {
            $uri = $menuItem->getRedirectUrl();
        } elseif ($menuItem->getInternalUrl()) {
            $uri = $this->serviceContainer->get('request')->getBaseUrl() . $menuItem->getInternalUrl();
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
        $item = $this->factory->createItem($menuItem->getName(), $options);

        if ($menuItem->isHidden()) {
            $item->setDisplay(false);
        }

        return $item;
    }

    /**
     * Create an new node using the ContentRoute object to generate the uri
     *
     * @param  \Networking\InitCmsBundle\Entity\MenuItem $node
     * @return \Knp\Menu\ItemInterface
     * @deprecated please use createMenuItem
     */
    public function createFromNode(MenuItem $node)
    {
        if ($node->getRedirectUrl()) {
            $uri = $node->getRedirectUrl();
        } elseif ($node->getInternalUrl()) {
            $uri = $node->getInternalUrl();
            try {
                $routeArray = $route = $this->router->matchRequest(Request::create($uri));

                if (is_array($routeArray) && array_key_exists('_route', $routeArray)) {
                    if (array_key_exists('_route_object', $routeArray)) {
                        $route = $routeArray['_route_object'];
                    } else {
                        $route = new CMSRoute(
                            null,
                            $routeArray['path'],
                            array('locale' => $this->serviceContainer->get('request')->getLocale())
                        );
                    }
                    $uri = $this->router->generate($route);

                }
            } catch (\Exception $e) {
                //do nothing
            }
        } else {
            if ($this->viewStatus == Page::STATUS_PUBLISHED) {
                if ($snapshot = $node->getPage()->getSnapshot()) {
                    $route = new CMSRoute(
                        null,
                        $snapshot->getPath(),
                        array('locale' => $this->serviceContainer->get('request')->getLocale())
                    );
                    $uri = $this->router->generate($route);
                } else {
                    return;
                }
            } else {
                $route = $node->getPage()->getRoute();
                $uri = $this->router->generate($route);
            }
        }

        $options = array(
            'uri' => $uri,
            'label' => $node->getName(),
            'attributes' => array(),
            'linkAttributes' => $node->getLinkAttributes(),
            'childrenAttributes' => array(),
            'labelAttributes' => array(),
            'extras' => array(),
            'display' => true,
            'displayChildren' => true,

        );
        $item = $this->factory->createItem($node->getName(), $options);

        if ($node->isHidden()) {
            $item->setDisplay(false);
        }

        return $item;
    }

    /**
     * @param $id
     * @return object
     */
    public function get($id)
    {
        return $this->serviceContainer->get($id);
    }


    /**
     * @param string $menuName
     * @return array|bool
     */
    public function getFullMenu($menuName)
    {
        $repository = $this->getMenuItemRepository();

        /** @var $mainMenu Menu */
        $mainMenu = $repository->findOneBy(
            array('name' => $menuName, 'locale' => $this->serviceContainer->get('request')->getLocale())
        );
        $menuIterator = $repository->getChildrenByStatus($mainMenu, false, null, 'ASC', false, $this->viewStatus);

        return $menuIterator;

    }

    /**
     * @param string $menuName
     * @param int $level
     * @return array|bool
     */
    public function getSubMenu($menuName, $level = 1)
    {
        $repository = $this->getMenuItemRepository();

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

        $menuIterator = $repository->getChildrenByStatus($currentParent, false, null, 'ASC', false, $this->viewStatus);

        return $menuIterator;
    }

    /**
     * @return \Networking\InitCmsBundle\Entity\MenuItemRepository
     */
    public function getMenuItemRepository()
    {
        $repository = $this->serviceContainer->get('doctrine')
            ->getRepository('NetworkingInitCmsBundle:MenuItem');

        return $repository;
    }

    /**
     * @param Menu $menu
     * @param array $menuIterator
     * @param int $startDepth
     * @return Menu
     */
    public function createMenu(Menu $menu, array $menuIterator, $startDepth)
    {
        foreach ($menuIterator as $childNode) {
            $this->addNodeToMenu($menu, $childNode, $startDepth);
        }

        return $menu;
    }

    /**
     * @param Menu $menu
     * @param array $node
     * @param $startDepth
     * @return \Knp\Menu\ItemInterface|bool
     */
    public function addNodeToMenu(Menu $menu, MenuItem $node, $startDepth)
    {

        if ($node->getVisibility() == MenuItem::VISIBILITY_PUBLIC || $this->isLoggedIn) {
            if ($node->getLvl() > $startDepth) {
                $menu = $this->getParentMenu($menu, $node, $startDepth);
            }
            if (is_object($menu)) {
                $knpMenuNode = $this->createFromMenuItem($node);
                if (!is_null($knpMenuNode)) {
                    $menu->addChild($knpMenuNode);
                    $knpMenuNode->setAttribute('class', $node->getLinkClass());
                    return $knpMenuNode;
                }
            }
        }

        return false;

    }
}
