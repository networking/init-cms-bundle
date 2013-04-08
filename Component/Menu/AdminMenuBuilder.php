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
    Networking\InitCmsBundle\Entity\Page,
    Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface,
    Networking\InitCmsBundle\Doctrine\Extensions\Versionable\ResourceVersionInterface;

/**
 * @author net working AG <info@networking.ch>
 */
class AdminMenuBuilder extends MenuBuilder
{
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

    public function createAdminMenu(Request $request)
    {
        // Default to homepage
        $liveRoute = null;

        $livePath = null;

        $draftRoute = null;

        $draftPath = null;

        $menu = false;

        $sonataAdmin = null;

        $entity = null;

        $defaultHome = $this->serviceContainer->get('router')->generate('networking_init_cms_default');

        if ($this->isLoggedIn) {

            $menu = $this->factory->createItem('root');

            $editPath = false;
            $menu->setChildrenAttribute('class', 'nav pull-right');

            $dashboardUrl = $this->router->generate('sonata_admin_dashboard');

            if ($sonataAdminParam = $request->get('_sonata_admin')) {

                $posibleAdmins = explode('|', $sonataAdminParam);

                foreach ($posibleAdmins as $adminCode) {
                    // we are in the admin area
                    $sonataAdmin = $this->serviceContainer->get($adminCode);
                    if ($id = $request->get('id')) {
                        $entity = $sonataAdmin->getObject($id);
                    }
                }

            } else {
                // we are in the frontend
                $entity = $request->get('_content');
            }

            if ($entity instanceof VersionableInterface) {
                if ($snapShot = $entity->getSnapshot()) {
                    $liveRoute = $this->router->generate($snapShot->getRoute());
                }
                $draftRoute = $this->router->generate($entity->getRoute());

                $editPath = $this->router->generate(
                    'admin_networking_initcms_page_edit',
                    array('id' => urlencode($entity->getId()))
                );
                $language = $entity->getRoute()->getLocale();
            } elseif ($entity instanceof ResourceVersionInterface) {
                $liveRoute = $this->router->generate($entity->getRoute());
                $draftRoute = $this->router->generate($entity->getPage()->getRoute());

                $editPath = $this->router->generate(
                    'admin_networking_initcms_page_edit',
                    array('id' => urlencode($entity->getPage()->getId()))
                );
                $language = $entity->getRoute()->getLocale();
            }

            if (!isset($language)) {
                $language = $request->getLocale();
            }


            if ($draftRoute) {
                $draftPath = $this->router->generate(
                    'networking_init_view_draft',
                    array('locale' => $language, 'path' => base64_encode($draftRoute))
                );
            }
            if ($liveRoute) {
                $livePath = $this->router->generate(
                    'networking_init_view_live',
                    array('locale' => $language, 'path' => base64_encode($liveRoute))
                );
            }

            $session = $this->serviceContainer->get('session');
            $lastActionUrl = $dashboardUrl;
            $lastActions = $session->get('_networking_initcms_admin_tracker');


            if ($lastActions) {
                $lastActionArray = json_decode($lastActions);
                if (count($lastActionArray)) {
                    if ($request->get('_route') == 'sonata_admin_dashboard' || $sonataAdmin) {
                        $lastAction = next($lastActionArray);
                    } else {
                        $lastAction = reset($lastActionArray);
                    }
                    if ($lastAction) {
                        $lastActionUrl = $lastAction->url;
                    }
                }
            }


            // Set active url based on which status is in the session
            if ($request->get('_route') == 'sonata_admin_dashboard' || $sonataAdmin) {
                $menu->setCurrentUri($lastActionUrl);
            } elseif ($this->serviceContainer->get('session')->get(
                '_viewStatus'
            ) === VersionableInterface::STATUS_PUBLISHED
            ) {
                $menu->setCurrentUri($livePath);
            } else {
                $menu->setCurrentUri($draftPath);
            }

            if ($editPath && !$sonataAdmin) {

                $menu->addChild(
                    'Edit',
                    array(
                        'label' => $this->serviceContainer->get(
                            'translator'
                        )->trans('Edit', array(), 'NetworkingInitCmsBundle'),
                        'uri' => $editPath
                    )
                );
                $this->addIcon($menu['Edit'], array('icon' => 'pencil icon-white', 'append' => false));
            }

            $menu->addChild('Admin', array('uri' => $lastActionUrl));

            $viewStatus = $this->serviceContainer->get('session')->get('_viewStatus');
            $translator = $this->serviceContainer->get('translator');
            $webLink = $translator->trans('link.website_', array(), 'NetworkingInitCmsBundle');

            if ($editPath && !$sonataAdmin) {
                $webLink = $translator->trans('link.website_' . $viewStatus, array(), 'NetworkingInitCmsBundle');

            }
            $dropdown = $this->createDropdownMenuItem(
                $menu,
                $webLink,
                true,
                array('caret' => true)
            );

            if ($draftPath) {
                $dropdown->addChild(
                    'Draft view',
                    array('uri' => $draftPath, 'linkAttributes' => array('class' => 'color-draft'))
                );
            }
            if ($livePath) {
                $dropdown->addChild('Live view', array('uri' => $livePath));
            }

            if (!$draftPath && !$livePath) {
                $dropdown->addChild('Home Page', array('uri' => $defaultHome));
            }

        }

        return $menu;
    }


}
