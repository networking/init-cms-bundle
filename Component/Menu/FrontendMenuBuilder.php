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
    Networking\InitCmsBundle\Component\Menu\MenuBuilder,
    Networking\InitCmsBundle\Entity\MenuItem as Menu,
    Networking\InitCmsBundle\Entity\Page;

/**
 * @author net working AG <info@networking.ch>
 */
class FrontendMenuBuilder extends MenuBuilder
{
    /**
     * Creates the main page navigation for the left side of the top frontend navigation
     *
     * @param Request $request
     * @param $menuName
     * @param string $classes
     * @return \Knp\Menu\ItemInterface|\Knp\Menu\MenuItem
     */
    public function createMainMenu(Request $request, $menuName, $classes = '')
    {
        $menu = $this->createNavbarMenuItem();
        $menu->setChildrenAttribute('class', $classes);
        $menu->setCurrentUri($request->getRequestUri());
        /** @var $mainMenu Menu */
        $menuIterator = $this->getFullMenu($menuName);

        if (!$menuIterator) {
            return $menu;
        }

        $startDepth = 1;
        $menu = $this->createMenu($menu, $menuIterator, $startDepth);
        return $menu;
    }


    /**
     * Create frontend sub navigation on the left hand side of the screen
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  string $menuName
     * @param string $classes
     * @return bool|\Knp\Menu\ItemInterface
     */
    public function createSubnavMenu(Request $request, $menuName, $classes = 'nav nav-tabs nav-stacked')
    {
        $menu = $this->createNavbarMenuItem();
        $menu->setChildrenAttribute('class', $classes);
        $menu->setCurrentUri($request->getRequestUri());

        /** @var $mainMenu Menu */
        $menuIterator = $this->getSubMenu($menuName, 1);

        if (!$menuIterator) {
            return $menu;
        }

        $startDepth = 2;
        $menu = $this->createMenu($menu, $menuIterator, $startDepth);
        $this->showOnlyCurrentChildren($menu);
        $this->setRecursiveChildrenAttribute($menu, array('class' => 'nav nav-list'));
        return $menu;
    }


    /**
     * Creates the login and change language navigation for the right side of the top frontend navigation
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $languages
     * @param string $classes
     * @return \Knp\Menu\ItemInterface
     */

    /**
     * Creates the login and change language navigation for the right side of the top frontend navigation
     *
     * @param Request $request
     * @param $languages
     * @param string $classes
     * @param bool $dropDownMenu
     * @return \Knp\Menu\ItemInterface
     */
    public function createFrontendLangMenu(
        Request $request,
        $languages,
        $classes = 'nav pull-right',
        $dropDownMenu = false
    )
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', $classes);

        if ($dropDownMenu) {
            $this->createDropdownLangMenu($menu, $languages, $request->getLocale());
        } else {
            $this->createInlineLangMenu($menu, $languages, $request->getLocale());
        }

        return $menu;
    }

    /**
     * Used to create a simple navigation for the footer
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $menuName
     * @param string $classes
     * @return \Knp\Menu\ItemInterface
     */
    public function createFooterMenu(Request $request, $menuName, $classes = '')
    {
        $menu = $this->createNavbarMenuItem();
        $menu->setChildrenAttribute('class', $classes);
        $menu->setCurrentUri($request->getRequestUri());
        /** @var $mainMenu Menu */
        $menuIterator = $this->getFullMenu($menuName);

        if (!$menuIterator) {
            return $menu;
        }

        $startDepth = 1;
        $menu = $this->createMenu($menu, $menuIterator, $startDepth);
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
    public function createDropdownLangMenu(
        &$menu,
        array $languages,
        $currentLanguage,
        $route = 'networking_init_change_language'
    )
    {
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

    public function createInlineLangMenu(
        &$menu,
        array $languages,
        $currentLanguage,
        $route = 'networking_init_change_language'
    )
    {
        foreach ($languages as $language) {
            $node = $menu->addChild(
                $language['label'],
                array(
                    'uri' => $this->router->generate($route, array('locale' => $language['locale'])),
                    'linkAttributes' => array('class' => 'language')
                )
            );
            if ($language['locale'] == $currentLanguage) {
                $node->setCurrent(true);
            }
        }
    }
}
