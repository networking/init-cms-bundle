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

use Symfony\Component\HttpFoundation\Request;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\ResourceVersionInterface;
use Networking\InitCmsBundle\Admin\Pool;

/**
 * @author net working AG <info@networking.ch>
 */
class AdminMenuBuilder extends MenuBuilder
{
    /**
     * @var Pool
     */
    protected $adminPool;

    public function setAdminPool($adminPool){
        $this->adminPool = $adminPool;
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

        $defaultHome = $this->router->generate('networking_init_cms_default');

        if ($this->isLoggedIn) {

            $menu = $this->factory->createItem('root');

            $editPath = false;
            $menu->setChildrenAttribute('class', 'nav pull-right');

            $dashboardUrl = $this->router->generate('sonata_admin_dashboard');

            if ($sonataAdminParam = $request->get('_sonata_admin')) {

                $possibleAdmins = explode('|', $sonataAdminParam);

                foreach ($possibleAdmins as $adminCode) {
                    // we are in the admin area
                    $sonataAdmin = $this->adminPool->getAdminByAdminCode($adminCode);
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

            $session = $this->request->getSession();
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
            } elseif ($this->request->getSession()->get(
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
                        'label' => $this->translator->trans('Edit', array(), 'NetworkingInitCmsBundle'),
                        'uri' => $editPath
                    )
                );
                $this->addIcon($menu['Edit'], array('icon' => 'pencil icon-white', 'append' => false));
            }

            $menu->addChild('Admin', array('uri' => $lastActionUrl));

            $viewStatus = $this->request->getSession()->get('_viewStatus');
            $translator = $this->translator;
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
