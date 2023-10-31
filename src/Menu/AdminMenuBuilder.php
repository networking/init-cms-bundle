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
use Knp\Menu\Matcher\MatcherInterface;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface;
use Networking\InitCmsBundle\Entity\ContentRoute;
use Networking\InitCmsBundle\Entity\ContentRouteManager;
use Networking\InitCmsBundle\Model\MenuItemManagerInterface;
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
    protected Pool $pool;

    protected array $languages = [];

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
        bool $allowLocaleCookie = true,
        array $languages = []
    ) {
        $this->pool = $pool;
        $this->languages = $languages;
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

    public function createAdminMenu(): bool|\Knp\Menu\ItemInterface
    {
        // Default to homepage
        $liveRoute = null;
        $draftRoute = null;
        $menu = $this->factory->createItem('root');
        $entity = null;

        $defaultHome = $this->router->generate('networking_init_cms_default');

        $class = 'nav navbar-nav';
        if ($this->request->isXmlHttpRequest()) {
            $class = 'initnav';
        }

        if ($this->isLoggedIn) {
            $menu->setChildrenAttribute('class', $class);
            if ($sonataAdminParam = $this->request->get('_sonata_admin')) {
                $possibleAdmins = explode('|', (string) $sonataAdminParam);
                foreach ($possibleAdmins as $adminCode) {
                    $sonataAdmin = $this->pool->getAdminByAdminCode($adminCode);
                    if ($id = $this->request->get('id')) {
                        $entity = $sonataAdmin->getObject($id);
                    }
                }
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
                        RouteObjectInterface::ROUTE_OBJECT => $entity->getRoute(),
                    ]
                );

                $language = $entity->getRoute()->getLocale();
            }

            if (!isset($language)) {
                $currentLocale = $this->request->getLocale();
                $language = $this->getDefaultLocale($currentLocale);
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



            if ($draftPath) {
                $menu->addChild(
                    'view_website.status_draft',
                    [
                        'uri' => $draftPath,
                        'extras' => [
                            'translation_domain' => 'NetworkingInitCmsBundle',
                            'color' => 'text-warning',
                            'icon' => 'ki-outline ki-notepad-edit',
                        ],
                    ]
                );
            }
            if ($livePath) {
                $menu->addChild(
                    'view_website.status_published',
                    [
                        'uri' => $livePath,
                        'extras' => [
                            'translation_domain' => 'NetworkingInitCmsBundle',
                            'color' => 'text-success',
                            'icon' => 'ki-outline ki-abstract-17',
                        ],
                    ]
                );
            }

            if (!$draftPath && !$livePath) {
                $menu->addChild(
                    'view_website.status_draft',
                    [
                        'uri' => $defaultHome,
                        'extras' => [
                            'translation_domain' => 'NetworkingInitCmsBundle',
                            'color' => 'text-warning',
                            'icon' => 'ki-outline ki-notepad-edit',
                        ],
                    ]
                );
                $menu->addChild(
                    'view_website.status_published',
                    [
                        'uri' => $defaultHome,
                        'extras' => [
                            'translation_domain' => 'NetworkingInitCmsBundle',
                            'color' => 'text-success',
                            'icon' => 'ki-outline ki-abstract-17',
                        ],
                    ]
                );
            }
        }


        return $menu;
    }

    public function createFrontendAdminMenu(): bool|\Knp\Menu\ItemInterface
    {
        // Default to homepage
        $liveRoute = null;

        $draftRoute = null;

        $menu = $this->factory->createItem('root');

        $sonataAdmin = null;

        $defaultHome = $this->router->generate('networking_init_cms_default');

        $adminLocale = $this->request->getSession()->get('admin/_locale');
        $class = 'navbar-nav me-3';

        if ($this->isLoggedIn) {
            $editPath = false;
            $menu->setChildrenAttribute('class', $class);

            $dashboardUrl = $this->router->generate('sonata_admin_dashboard');

            $entity = $this->request->get('_content');

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
                        RouteObjectInterface::ROUTE_OBJECT => $entity->getRoute(),
                    ]
                );

                $pageAdmin = $this->pool->getAdminByAdminCode(
                    'networking_init_cms.admin.page'
                );
                $editPath = $pageAdmin->generateObjectUrl('edit', $entity);

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
                $lastActionArray = json_decode((string) $lastActions, null, 512, JSON_THROW_ON_ERROR);
                if (is_countable($lastActionArray) ? count($lastActionArray) : 0) {
                    if ('sonata_admin_dashboard'
                        == $this->request->get('_route')
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
                && 'sonata_admin_dashboard' != $this->request->get('_route')
            ) {
                $menu->addChild('Admin', ['uri' => $lastActionUrl]);
            }

            $firstItemStatus = !$sonataAdmin ? $this->request->getSession()
                ->get('_viewStatus') : '';
            $dropdown = $menu->addChild(
                'link.website_'.$firstItemStatus,
                [
                    'extras' => ['translation_domain' => 'NetworkingInitCmsBundle'],
                ]
            );

            if ($draftPath) {
                $dropdown->addChild(
                    'view_website.status_draft',
                    [
                        'uri' => $draftPath,
                        'linkAttributes' => ['class' => 'text-warning'],
                        'extras' => ['translation_domain' => 'NetworkingInitCmsBundle'],
                    ]
                );
            }
            if ($livePath) {
                $dropdown->addChild(
                    'view_website.status_published',
                    [
                        'uri' => $livePath,
                        'linkAttributes' => ['class' => 'text-success'],
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

    public function getDefaultLocale($locale): string
    {
        foreach ($this->languages as $language) {
            if (str_starts_with($language['locale'], substr($locale, 0, 2))) {
                return $language['locale'];
            }
        }

        return $this->languages[0]['locale'];
    }

    protected function addIcon($item, $icon)
    {
        $iconTag = ' <i class="fa fa-'.$icon['icon'].'"></i>';
        if (!isset($icon['append']) || true === $icon['append']) {
            $label = $item->getLabel().' '.$iconTag;
        } else {
            $label = $iconTag.' '.$item->getLabel();
        }
        $item->setLabel($label)
            ->setExtra('safe_label', true);

        return $item;
    }

    /**
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
