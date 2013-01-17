<?php

/*
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
    Knp\Menu\Iterator\RecursiveItemIterator,
    Networking\InitCmsBundle\Entity\MenuItem as Menu,
    Networking\InitCmsBundle\Entity\MenuItemRepository,
    Networking\InitCmsBundle\Component\Menu\MenuSubItemFilterIterator,
    Networking\InitCmsBundle\Entity\Page;

/**
 * @author net working AG <info@networking.ch>
 */
class NavbarMenuBuilder extends AbstractNavbarMenuBuilder
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
     * @param \Knp\Menu\FactoryInterface                                $factory
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     * @param \Symfony\Component\DependencyInjection\Container          $serviceContainer
     */
    public function __construct(
        FactoryInterface $factory,
        SecurityContextInterface $securityContext,
        Container $serviceContainer
    ) {
        parent::__construct($factory);

        $this->securityContext = $securityContext;
        if ($this->securityContext->isGranted('IS_AUTHENTICATED_FULLY') || $this->securityContext->isGranted(
            'IS_AUTHENTICATED_REMEMBERED'
        )
        ) {
            $this->isLoggedIn = true;
        }
        $this->serviceContainer = $serviceContainer;
        $this->router = $this->serviceContainer->get('router');

        $this->viewStatus = $serviceContainer->get('session')->get('_viewStatus')
            ? $serviceContainer->get('session')->get('_viewStatus')
            : Page::STATUS_PUBLISHED;
    }

    /**
     * Creates the main page navigation for the left side of the top frontend navigation
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $path
     * @return \Knp\Menu\ItemInterface
     */
    public function createMainMenu(Request $request, $path)
    {

        /** @var $repository MenuItemRepository */
        $repository = $this->serviceContainer->get('doctrine')
            ->getRepository('NetworkingInitCmsBundle:MenuItem');
        $menu = $this->createNavbarMenuItem();
        $menu->setCurrentUri($request->getRequestUri());

        /** @var $mainMenu Menu */
        $mainMenu = $repository->findOneBy(
            array('name' => $path, 'locale' => $this->serviceContainer->get('request')->getLocale())
        );

        if (!$mainMenu) {
            return $menu;
        }

        $menuIterator = new \RecursiveIteratorIterator(
            new RecursiveItemIterator($mainMenu->getChildrenByStatus($this->viewStatus)),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($menuIterator as $childNode) {
            if ($menuIterator->getDepth() > 0) {
                $parentMenu = $this->getMenuParentItem($menu, $childNode, $menuIterator->getDepth());
                $parentMenu->addChild($this->createFromNode($childNode));
            } else {
                $menu->addChild($this->createFromNode($childNode));
            }
        }

        return $menu;
    }

    /**
     * Creates the login and change language navigation for the right side of the top frontend navigation
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $languages
     * @return \Knp\Menu\ItemInterface
     */
    public function createCMSMenu(Request $request, $languages)
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav pull-right');

        if ($this->isLoggedIn) {
            $menu->addChild($this->get('translator')->trans('logout'), array('route' => 'fos_user_security_logout'));
            if ($this->securityContext->isGranted('ROLE_SONATA_ADMIN')) {
                $menu->addChild('CMS Admin', array('route' => 'sonata_admin_dashboard'));
            }
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
     * Creates the login and change language navigation for the right side of the top frontend navigation
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $languages
     * @return \Knp\Menu\ItemInterface
     */
    public function createFrontendLangMenu(Request $request, $languages)
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav pull-right');


        $this->createNavbarsLangMenu($menu, $languages, $request->getLocale());

        return $menu;
    }

    /**
     * Creates a language navigation for the admin area of the website
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $languages
     * @return \Knp\Menu\ItemInterface
     */
    public function createAdminLangMenu(Request $request, $languages)
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav pull-right');

        $this->createNavbarsLangMenu($menu, $languages, $request->getLocale(), 'networking_init_change_admin_language');

        return $menu;
    }

    /**
     * Create frontend sub navigation on the left hand side of the screen
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  string                                    $path
     * @return bool|\Knp\Menu\ItemInterface
     */
    public function createNavbarsSubnavMenu(Request $request, $path = '#main#')
    {
        $root = null;
        /** @var $repository MenuItemRepository */
        $repository = $this->serviceContainer->get('doctrine')
            ->getRepository('NetworkingInitCmsBundle:MenuItem');

        $menu = $this->factory->createItem('sub_nav');
        $menu->setCurrentUri($request->getRequestUri());

        /** @var $mainMenu Menu */
        $mainMenu = $repository->findOneBy(
            array('name' => $path, 'locale' => $this->serviceContainer->get('request')->getLocale())
        );

        if (!$mainMenu) {
            return $menu;
        }

        /** @var $menuIterator \ArrayIterator */
        $menuIterator = new \RecursiveIteratorIterator(
            new RecursiveItemIterator($mainMenu->getChildren()),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $iterator = new MenuSubItemFilterIterator($menuIterator, $request->getPathInfo());

        foreach ($iterator as $item) {
            $root = $item->getParentByLevel(2);
        }

        if (!$root) {
            return false;
        }

        $menuIterator = new \RecursiveIteratorIterator(
            new RecursiveItemIterator($root->getChildren()),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $menu->setChildrenAttribute('class', 'nav nav-tabs nav-stacked');

        foreach ($menuIterator as $childNode) {
            if ($menuIterator->getDepth() > 0) {
                $parentMenu = $this->getMenuParentItem($menu, $childNode, $menuIterator->getDepth());
                $parentMenu->addChild($this->createFromNode($childNode));
                $parentMenu->setChildrenAttribute('class', 'nav nav-list');
            } else {
                $menu->addChild($this->createFromNode($childNode));
            }
        }

        return $menu;
    }

    /**
     * Recursively get parents
     *
     * @param $menu
     * @param $childNode
     * @param $depth
     * @param  int   $level
     * @return mixed
     */
    public function getMenuParentItem($menu, $childNode, $depth, $level = 0)
    {
        if ($depth === $level) {
            return $menu->offsetGet($childNode->getName());
        }

        return $this->getMenuParentItem($menu, $childNode->getParent(), $depth, ++$level);
    }

    /**
     * Create an new node using the ContentRoute object to generate the uri
     *
     * @param  \Networking\InitCmsBundle\Entity\MenuItem $node
     * @return \Knp\Menu\ItemInterface
     */
    public function createFromNode(Menu $node)
    {
        $linkAttributes = array();
        if ($node->getRedirectUrl()) {
            $uri = $node->getRedirectUrl();
            $linkAttributes['target'] = '_blank';
        } else {
            if ($this->viewStatus == Page::STATUS_PUBLISHED) {
                if ($snapshot = $node->getPage()->getSnapshot()) {
                    $route = $snapshot->getRoute();
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
            'linkAttributes' => $linkAttributes,
            'childrenAttributes' => array(),
            'labelAttributes' => array(),
            'extras' => array(),
            'display' => true,
            'displayChildren' => true,

        );
        $item = $this->factory->createItem($node->getName(), $options);

        return $item;
    }

    /**
     * Used to create nodes for the language navigation in the front- and backend
     *
     * @param $menu
     * @param array $languages
     * @param $currentLanguage
     * @param string $route
     */
    public function createNavbarsLangMenu(
        &$menu,
        array $languages,
        $currentLanguage,
        $route = 'networking_init_change_language'
    ) {
        $this->addDivider($menu, true);

        $dropdown = $this->createDropdownMenuItem(
            $menu,
            $this->serviceContainer->get('translator')->trans('Change Language'),
            true,
            array('icon' => 'caret')
        );

        foreach ($languages as $language) {
            $node = $dropdown->addChild(
                $language['label'],
                array('uri' => $this->router->generate($route, array('locale' => $language['locale'])))
            );

            if ($language['locale'] == $currentLanguage) {
                $node->setCurrent(true);
            }
        }

    }

    /**
     * @param $id
     * @return object
     */
    public function get($id)
    {
        return $this->serviceContainer->get($id);
    }
}
