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

use Knp\Menu\FactoryInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Networking\InitCmsBundle\Model\ContentRoute;
use Networking\InitCmsBundle\Model\ContentRouteManager;
use Networking\InitCmsBundle\Model\MenuItemManagerInterface;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\ResourceVersionInterface;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AdminMenuBuilder.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class AdminMenuBuilder extends MenuBuilder
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * AdminMenuBuilder constructor.
     *
     * @param FactoryInterface              $factory
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param RequestStack                  $requestStack
     * @param RouterInterface               $router
     * @param MenuItemManagerInterface      $menuManager
     * @param TranslatorInterface           $translator
     * @param MatcherInterface              $matcher
     * @param Pool                          $pool
     * @param bool                          $allowLocaleCookie
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
        Pool $pool,
        $allowLocaleCookie = true
    ) {
        $this->pool = $pool;
        parent::__construct(
            $factory,
            $tokenStorage,
            $authorizationChecker,
            $requestStack,
            $router,
            $menuManager,
            $translator,
            $matcher,
            $allowLocaleCookie
        );
    }

    /**
     * @return bool|\Knp\Menu\ItemInterface
     */
    public function createAdminMenu()
    {

        // Default to homepage
        $liveRoute = null;

        $livePath = null;

        $draftRoute = null;

        $draftPath = null;

        $menu = $this->factory->createItem('root');

        $sonataAdmin = null;

        $entity = null;

        $defaultHome = $this->router->generate('networking_init_cms_default');

        $adminLocale = $this->request->getSession()->get('admin/_locale');
        $class = 'nav navbar-nav';
        if ($this->request->isXmlHttpRequest()) {
            $class = 'initnav';
        }

        if ($this->isLoggedIn) {
            $editPath = false;
            $menu->setChildrenAttribute('class', $class.' pull-right');

            $dashboardUrl = $this->router->generate('sonata_admin_dashboard');

            if ($sonataAdminParam = $this->request->get('_sonata_admin')) {
                $possibleAdmins = explode('|', $sonataAdminParam);

                foreach ($possibleAdmins as $adminCode) {
                    // we are in the admin area
                    $sonataAdmin = $this->pool->getAdminByAdminCode($adminCode);
                    if ($id = $this->request->get('id')) {
                        $entity = $sonataAdmin->getObject($id);
                    }
                }
            } else {
                // we are in the frontend
                $entity = $this->request->get('_content');
            }

            if ($entity instanceof VersionableInterface) {
                if ($snapShot = $entity->getSnapshot()) {
                    $liveRoute = $this->router->generate(
                        RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                        [
                            RouteObjectInterface::ROUTE_OBJECT => $this->getRoute(
                                $snapShot->getRoute()
                            ),
                        ]
                    );
                }

                $draftRoute = $this->router->generate(
                        RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                        [
                            RouteObjectInterface::ROUTE_OBJECT => $entity->getRoute()
                        ]
                    );

                $pageAdmin = $this->pool->getAdminByAdminCode(
                    'networking_init_cms.admin.page'
                );
                $editPath = $pageAdmin->generateObjectUrl('edit', $entity);

                $language = $entity->getRoute()->getLocale();
            } elseif ($entity instanceof ResourceVersionInterface) {
                $liveRoute = $this->router->generate(
                    $this->getRoute($entity->getRoute())
                );
                $draftRoute = $this->router->generate(
                    $this->getRoute($entity->getPage()->getRoute())
                );

                $pageAdmin = $this->pool->getAdminByAdminCode(
                    'networking_init_cms.admin.page'
                );
                $editPath = $pageAdmin->generateObjectUrl(
                    'edit',
                    $entity->getPage()
                );

                $language = $entity->getRoute()->getLocale();
            }

            if (!isset($language)) {
                $language = $this->request->getLocale();
            }

            if ($draftRoute) {
                $draftPath = $this->router->generate(
                    'networking_init_view_draft',
                    [
                        'locale' => $language,
                        'path' => base64_encode($draftRoute),
                    ]
                );
            } else {
                $draftPath = $this->router->generate(
                    'networking_init_view_draft',
                    [
                        'locale' => $language,
                        'path' => base64_encode($this->request->getBaseUrl()),
                    ]
                );
            }
            if ($liveRoute) {
                $livePath = $this->router->generate(
                    'networking_init_view_live',
                    ['locale' => $language, 'path' => base64_encode($liveRoute)]
                );
            } else {
                $livePath = $this->router->generate(
                    'networking_init_view_live',
                    [
                        'locale' => $language,
                        'path' => base64_encode($this->request->getBaseUrl()),
                    ]
                );
            }

            $session = $this->request->getSession();
            $lastActionUrl = $dashboardUrl;
            $lastActions = $session->get('_networking_initcms_admin_tracker');

            if ($lastActions) {
                $lastActionArray = json_decode($lastActions);
                if (count($lastActionArray)) {
                    if ($this->request->get('_route')
                        == 'sonata_admin_dashboard'
                        || $sonataAdmin
                    ) {
                        $lastAction = next($lastActionArray);
                    } else {
                        $lastAction = reset($lastActionArray);
                    }
                    if ($lastAction) {
                        $lastActionUrl = $lastAction->url;
                    }
                }
            }

            if ($editPath && !$sonataAdmin) {
                $menu->addChild(
                    'Edit',
                    [
                        'label' => $this->translator->trans(
                            'link_action_edit',
                            [],
                            'SonataAdminBundle',
                            $adminLocale
                        ),
                        'uri' => $editPath,
                    ]
                );
                $this->addIcon(
                    $menu['Edit'],
                    ['icon' => 'edit', 'append' => false]
                );
            }
            if (!$sonataAdmin
                && $this->request->get('_route') != 'sonata_admin_dashboard'
            ) {
                $menu->addChild('Admin', ['uri' => $lastActionUrl]);
            }

            $firstItemStatus = !$sonataAdmin ? $this->request->getSession()
                ->get('_viewStatus') : '';
            $dropdown = $menu->addChild(
                'link.website_'.$firstItemStatus,
                [
                    'dropdown' => true,
                    'caret' => true,
                    'extras' => ['translation_domain' => 'NetworkingInitCmsBundle'],
                ]
            );


            if ($draftPath) {
                $dropdown->addChild(
                    'view_website.status_draft',
                    [
                        'uri' => $draftPath,
                        'linkAttributes' => ['class' => 'color-draft'],
                        'extras' => ['translation_domain' => 'NetworkingInitCmsBundle'],
                    ]
                );
            }
            if ($livePath) {
                $dropdown->addChild(
                    'view_website.status_published',
                    [
                        'uri' => $livePath,
                        'linkAttributes' => ['class' => 'color-published'],
                        'extras' => ['translation_domain' => 'NetworkingInitCmsBundle'],
                    ]
                );
            }

            if (!$draftPath && !$livePath) {
                $dropdown->addChild(
                    'view_website.status_draft',
                    [
                        'uri' => $defaultHome,
                        'linkAttributes' => ['class' => 'color-draft'],
                        'extras' => ['translation_domain' => 'NetworkingInitCmsBundle'],
                    ]
                );
                $dropdown->addChild(
                    'view_website.status_published',
                    [
                        'uri' => $defaultHome,
                        'extras' => ['translation_domain' => 'NetworkingInitCmsBundle'],
                    ]
                );
            }
        }

        return $menu;
    }


    /**
     * @param $item
     * @param $icon
     *
     * @return mixed
     */
    protected function addIcon($item, $icon)
    {
        $icon = array_merge(
            ['tag' => (isset($icon['glyphicon'])) ? 'span' : 'i'],
            $icon
        );
        $addclass = '';
        if (isset($icon['inverted']) && $icon['inverted'] === true) {
            $addclass = ' icon-white';
        }
        $classicon = (isset($icon['glyphicon'])) ? ' class="'.$icon['glyphicon']
            : ' class="far fa-'.$icon['icon'];
        $myicon = ' <'.$icon['tag'].$classicon.$addclass.'"></'.$icon['tag']
            .'>';
        if (!isset($icon['append']) || $icon['append'] === true) {
            $label = $item->getLabel().' '.$myicon;
        } else {
            $label = $myicon.' '.$item->getLabel();
        }
        $item->setLabel($label)
            ->setExtra('safe_label', true);

        return $item;
    }

    /**
     * @param ContentRoute $contentRoute
     *
     * @return \Networking\InitCmsBundle\Component\Routing\Route
     */
    protected function getRoute(ContentRoute $contentRoute)
    {
        return ContentRouteManager::generateRoute(
            $contentRoute,
            $contentRoute->getPath(),
            ''
        );
    }
}
