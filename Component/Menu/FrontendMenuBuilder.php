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
    Knp\Menu\Iterator\RecursiveItemIterator,
    Networking\InitCmsBundle\Component\Menu\MenuBuilder,
    Networking\InitCmsBundle\Entity\MenuItem as Menu,
    Networking\InitCmsBundle\Entity\MenuItemRepository,
    Networking\InitCmsBundle\Component\Menu\MenuSubItemFilterIterator,
    Networking\InitCmsBundle\Entity\Page;

/**
 * @author net working AG <info@networking.ch>
 */
class FrontendMenuBuilder extends MenuBuilder
{


    /**
     * Creates the main page navigation for the left side of the top frontend navigation
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $path
     * @return \Knp\Menu\ItemInterface
     */
    public function createMainMenu(Request $request, $path, $classes = '')
    {

        /** @var $repository MenuItemRepository */
        $repository = $this->serviceContainer->get('doctrine')
            ->getRepository('NetworkingInitCmsBundle:MenuItem');
        $menu = $this->createNavbarMenuItem();
        $menu->setChildrenAttribute('class', $classes);
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
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $languages
     * @param string $classes
     * @return \Knp\Menu\ItemInterface
     */
    public function createFrontendLangMenu(Request $request, $languages, $classes = 'nav pull-right')
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', $classes);


        $this->createNavbarsLangMenu($menu, $languages, $request->getLocale());

        return $menu;
    }

    /**
     * Create frontend sub navigation on the left hand side of the screen
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  string                                    $path
     * @param string $classes
     * @return bool|\Knp\Menu\ItemInterface
     */
    public function createNavbarsSubnavMenu(Request $request, $path, $classes = 'nav nav-tabs nav-stacked')
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
            $root = $item->getParentByLevel(1);
        }

        if (!$root) {
            return false;
        }

        $menuIterator = new \RecursiveIteratorIterator(
            new RecursiveItemIterator($root->getChildren()),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $menu->setChildrenAttribute('class', $classes);

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
     * Used to create a simple navigation for the footer
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $path
     * @param string $classes
     * @return \Knp\Menu\ItemInterface
     */
    public function createFooterMenu(Request $request, $path, $classes = '')
    {
        $repository = $this->serviceContainer->get('doctrine')->getRepository('NetworkingInitCmsBundle:MenuItem');
        $menu = $this->createNavbarMenuItem();
        $menu->setChildrenAttribute('class', $classes);
        $menu->setCurrentUri($request->getRequestUri());
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
}
