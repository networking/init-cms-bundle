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

namespace Networking\InitCmsBundle\Controller;

use Networking\InitCmsBundle\Admin\PageAdmin;
use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Networking\InitCmsBundle\Component\Routing\Route;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface;
use Networking\InitCmsBundle\Entity\ContentRouteManager;
use Networking\InitCmsBundle\Entity\PageSnapshot;
use Networking\InitCmsBundle\Helper\LanguageSwitcherHelper;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Networking\InitCmsBundle\Model\PageSnapshotInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class FrontendPageController.
 *
 * @author net working AG <info@networking.ch>
 */
class FrontendPageController extends AbstractController
{
    /**
     * FrontendPageController constructor.
     */
    public function __construct(
        protected PageCacheInterface $pageCache,
        protected TokenStorageInterface $tokenStorage,
        protected AuthorizationCheckerInterface $authorizationChecker,
        protected LanguageSwitcherHelper $languageSwitcherHelper,
        protected PageManagerInterface $pageManager,
        protected PageHelper $pageHelper,
        protected Pool $pool,
    ) {
    }

    /**
     * @throws InvalidArgumentException|\JsonException
     */
    public function indexAction(Request $request)
    {
        return $this->index($request);
    }

    /**
     * @return mixed|string|RedirectResponse|Response|null
     *
     * @throws InvalidArgumentException|\JsonException|\Exception
     */
    public function index(Request $request): mixed
    {
        /** @var PageSnapshotInterface $page */
        $page = $request->get('_content');

        $template = $request->get('_template');
        if ($template instanceof Template) {
            $template = $template->template;
        }
        $user = $this->getUser();

        if ($this->pageCache->isCacheable($request, $user)
            && $page instanceof PageSnapshotInterface
        ) {
            if (!$this->isSnapshotActive($page)) {
                throw new NotFoundHttpException();
            }

            if (PageInterface::VISIBILITY_PUBLIC
                != $this->getSnapshotVisibility($page)
            ) {
                if (false === $this->authorizationChecker->isGranted(
                    'ROLE_USER'
                )
                ) {
                    throw new AccessDeniedException();
                }
            }

            $updatedAt = $this->pageCache->get(
                sprintf('page_%s_created_at', $page->getId())
            );
            $cacheKey = 'page_'.$request->getLocale().str_replace(
                '/',
                '',
                $request->getPathInfo()
            );

            if ($updatedAt != $page->getSnapshotDate()) {
                $this->pageCache->delete($cacheKey);
            }

            $response = $this->pageCache->get($cacheKey);

            if (!$response instanceof Response) {
                $params = $this->getPageParameters($request);

                if ($params instanceof RedirectResponse) {
                    $this->addResponseLocaleCookies(
                        $params,
                        $request->getLocale()
                    );

                    return $params;
                }
                $html = $this->renderView(
                    $template,
                    $params
                );
                $response = new Response($html);
                $response->headers->set('X-Init-Cms-Cache', 'true');

                $this->pageCache->set($cacheKey, $response);
                $this->pageCache->set(
                    sprintf('page_%s_created_at', $page->getId()),
                    $page->getSnapshotDate()
                );
            }
        } else {
            $params = $this->getPageParameters($request);

            if ($params instanceof RedirectResponse) {
                $this->addResponseLocaleCookies($params, $request->getLocale());

                return $params;
            }
            $html = $this->renderView(
                $template,
                $params
            );
            $response = new Response($html);
        }

        $this->addResponseLocaleCookies($response, $request->getLocale());

        return $response;
    }

    private function addResponseLocaleCookies(Response $response, $locale): void
    {
        if ($this->getPageHelper()->isAllowLocaleCookie()
            && !$this->getPageHelper()->isSingleLanguage()
        ) {
            $cookies = $this->pageHelper->setLocaleCookies($locale);
            foreach ($cookies as $cookie) {
                $response->headers->setCookie($cookie);
            }
        }
    }

    /**
     * Check if page is an alias for another page.
     */
    public function getRedirect(
        Request $request,
        PageInterface $page
    ): bool|RedirectResponse {
        if (method_exists($page, 'getAliasFullPath')) {
            if ($page->getAliasFullPath()) {
                $baseUrl = $request->getBaseUrl();

                return new RedirectResponse($baseUrl.$page->getAliasFullPath());
            }
        }

        return false;
    }

    /**
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface
     */
    public function getPageParameters(Request $request): array|bool|RedirectResponse
    {
        $page = $request->get('_content');

        if (is_null($page)) {
            throw $this->createNotFoundException('no page object found');
        }

        if ($page instanceof PageSnapshot) {
            return $this->getLiveParameters($request, $page);
        }

        return $this->getDraftParameters($request, $page);
    }

    public function getDraftParameters(
        Request $request,
        PageInterface $page
    ): array|bool|RedirectResponse {
        if ($redirect = $this->getRedirect($request, $page)) {
            return $redirect;
        }

        if (PageInterface::VISIBILITY_PUBLIC != $page->getVisibility()) {
            if (false === $this->authorizationChecker->isGranted('ROLE_USER')) {
                throw new AccessDeniedException();
            }
        }

        if (VersionableInterface::STATUS_PUBLISHED != $page->getStatus()) {
            if (!$this->tokenStorage->getToken()
                || false === $this->authorizationChecker->isGranted(
                    'ROLE_SONATA_ADMIN'
                )
            ) {
                $message = 'The requested page has the status "'
                    .$page->getStatus().'", please login to view page';
                throw $this->createNotFoundException($message);
            }
        }

        return ['page' => $page];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getLiveParameters(
        Request $request,
        PageSnapshot $pageSnapshot
    ): RedirectResponse|bool|array {
        /** @var $page PageInterface */
        $page = $this->getPageHelper()->unserializePageSnapshotData(
            $pageSnapshot,
            false
        );

        if ($redirect = $this->getRedirect($request, $page)) {
            return $redirect;
        }

        if (PageInterface::VISIBILITY_PUBLIC != $page->getVisibility()) {
            if (false === $this->container->get(
                'security.authorization_checker'
            )->isGranted('ROLE_USER')
            ) {
                throw new AccessDeniedException();
            }
        }

        return ['page' => $page];
    }

    /**
     * Redirect to the admin dashboard.
     */
    public function adminAction(): RedirectResponse
    {
        $url = $this->generateUrl('sonata_admin_dashboard');

        return $this->redirect($url);
    }

    /**
     * @throws InvalidArgumentException|\JsonException
     */
    public function home(Request $request): RedirectResponse|string|Response|null
    {
        if ('networking_init_cms_default' === $request->get('_route')) {
            $request = $this->getPageHelper()->matchContentRouteRequest(
                $request
            );
        }

        return $this->index($request);
    }

    /**
     * @throws InvalidArgumentException|\JsonException
     */
    public function homeAction(Request $request): RedirectResponse|string|Response|null
    {
        if ('networking_init_cms_default' === $request->get('_route')) {
            $request = $this->getPageHelper()->matchContentRouteRequest(
                $request
            );
        }

        return $this->index($request);
    }

    /**
     * Change the language in the admin area (currently not implemented in the template).
     */
    public function changeAdminLanguageAction(
        Request $request,
        $locale
    ): RedirectResponse {
        $request->getSession()->set('admin/_locale', $locale);

        return new RedirectResponse($request->headers->get('referer'));
    }

    /**
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface
     */
    public function changeLanguageAction(Request $request, $oldLocale, $locale): RedirectResponse
    {
        $params = [];
        $routeName = 'networking_init_cms_default';

        $translationRoute = $this->getTranslationRoute(
            $request->headers->get('referer'),
            $oldLocale,
            $locale
        );

        $request->setLocale($locale);

        if (is_array($translationRoute)) {
            $routeName = $translationRoute['_route'];
            unset($translationRoute['_route']);
            foreach ($translationRoute as $key => $var) {
                $params[$key] = $var;
            }
        }

        if ($translationRoute instanceof RouteObjectInterface) {
            $routeName = RouteObjectInterface::OBJECT_BASED_ROUTE_NAME;
            $params[RouteObjectInterface::ROUTE_OBJECT] = $translationRoute;
        }

        if (is_string($translationRoute)) {
            $routeName = $translationRoute;
        }

        $params['_locale'] = $locale;

        $parts = parse_url($request->headers->get('referer', '/'));

        if (array_key_exists('query', $parts) && $parts['query']) {
            parse_str($parts['query'], $query);

            $params = array_merge($query, $params);
        }

        $newURL = $this->container->get('router')->generate(
            $routeName,
            $params
        );

        $response = new RedirectResponse($newURL);

        if ($this->getPageHelper()->isAllowLocaleCookie()) {
            $cookies = $this->pageHelper->setLocaleCookies($locale);
            foreach ($cookies as $cookie) {
                $response->headers->setCookie($cookie);
            }
        }

        return $response;
    }

    public function viewDraftAction(
        Request $request,
        $locale = null,
        $path = null
    ): RedirectResponse {
        if ($locale) {
            $request->setLocale($locale);
        }

        return $this->changeViewMode(
            $request,
            VersionableInterface::STATUS_DRAFT,
            $path
        );
    }

    public function viewLiveAction(
        Request $request,
        $locale = null,
        $path = null
    ): RedirectResponse {
        if ($locale) {
            $request->setLocale($locale);
        }

        return $this->changeViewMode(
            $request,
            VersionableInterface::STATUS_PUBLISHED,
            $path
        );
    }

    /**
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|AccessDeniedException
     */
    protected function changeViewMode(Request $request, $status, $path): RedirectResponse
    {
        if (false === $this->authorizationChecker->isGranted(
            'ROLE_SONATA_ADMIN'
        )
        ) {
            $message = 'Please login to carry out this action';
            throw new AccessDeniedException($message);
        }

        $request->getSession()->set('_viewStatus', $status);

        if ($path) {
            $url = base64_decode((string) $path);
        } else {
            $url = $this->container->get('router')->generate(
                'networking_init_cms_default'
            );
        }

        $response = $this->redirect($url);

        if ($this->getPageHelper()->isAllowLocaleCookie()) {
            $cookies = $this->pageHelper->setLocaleCookies(
                $request->getLocale()
            );
            foreach ($cookies as $cookie) {
                $response->headers->setCookie($cookie);
            }
        }

        return $response;
    }

    /**
     * get the route for the translation of a given page, the referrer page.
     */
    protected function getTranslationRoute(
        $referrer,
        $oldLocale,
        $locale
    ): array|\Symfony\Cmf\Component\Routing\RouteObjectInterface|string {
        $oldURL = $this->languageSwitcherHelper->getPathInfo($referrer);

        return $this->languageSwitcherHelper->getTranslationRoute(
            $oldURL,
            $oldLocale,
            $locale
        );
    }

    public function translationNotFoundAction(Request $request): Response
    {
        $params = [
            'language' => \Locale::getDisplayLanguage($request->getLocale()),
        ];

        return $this->render(
            $this->getParameter('networking_init_cms.no_translation_template'),
            $params
        );
    }

    public function pageNotFoundAction(): Response
    {
        return $this->render(
            $this->getParameter('networking_init_cms.404_template')
        );
    }

    /**
     * @throws \JsonException
     */
    public function adminNavbarAction(Request $request, $page_id = null): Response
    {
        if ($page_id) {
            $page = $this->pageManager->find($page_id);
            $request->attributes->set('_content', $page);
        }

        $entity = $request->get('_content');

        $draftPath = null;
        $livePath = null;
        $editPath = null;
        $liveRoute = null;
        $locale = $request->getLocale();
        $shortLocale = substr($locale, 0, 2);

        $allowedLocales = $this->getParameter('networking_init_cms.page.languages');

        $localeMatched = false;

        foreach ($allowedLocales as $allowedLocale) {
            if ($allowedLocale['locale'] == $request->getLocale() || $shortLocale == $allowedLocale['locale']) {
                $locale = $allowedLocale['locale'];
                $localeMatched = true;
            }
        }

        if (!$localeMatched) {
            $locale = $allowedLocales[0]['locale'];
        }

        if ($entity instanceof VersionableInterface) {
            if ($snapShot = $entity->getSnapshot()) {
                $routeObject = ContentRouteManager::generateRoute(
                    $snapShot->getRoute(),
                    $snapShot->getRoute()->getPath(),
                    ''
                );
                $liveRoute = $this->generateUrl(
                    RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                    [
                        RouteObjectInterface::ROUTE_OBJECT => $routeObject,
                    ]
                );
            }

            $locale = $entity->getRoute()->getDefault(Route::LOCALE);

            if ($liveRoute) {
                $livePath = $this->generateUrl(
                    'networking_init_view_live',
                    ['locale' => $locale, 'path' => base64_encode($liveRoute)]
                );
            }

            $draftRoute = $this->generateUrl(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                [
                    RouteObjectInterface::ROUTE_OBJECT => $entity->getRoute(),
                ]
            );

            $draftPath = $this->generateUrl(
                'networking_init_view_draft',
                [
                    'locale' => $locale,
                    'path' => base64_encode($draftRoute),
                ]
            );

            $pageAdmin = $this->pool->getAdminByAdminCode(
                'networking_init_cms.admin.page'
            );
            $editPath = $pageAdmin->generateObjectUrl('edit', $entity);
        }

        if (!$draftPath) {
            $draftPath = $this->generateUrl(
                'networking_init_view_draft',
                [
                    'locale' => $locale,
                    'path' => base64_encode($request->getBaseUrl()),
                ]
            );
        }

        if (!$livePath) {
            $livePath = $this->generateUrl(
                'networking_init_view_live',
                [
                    'locale' => $locale,
                    'path' => base64_encode($request->getBaseUrl()),
                ]
            );
        }

        $lastActionUrl = $this->generateUrl('sonata_admin_dashboard');
        $lastActions = $request->getSession()->get(
            '_networking_initcms_admin_tracker'
        );

        if ($lastActions) {
            $lastActionArray = json_decode(
                (string) $lastActions,
                null,
                512,
                JSON_THROW_ON_ERROR
            );
            if (is_countable($lastActionArray) ? count($lastActionArray) : 0) {
                $lastAction = reset($lastActionArray);

                if ($lastAction) {
                    $lastActionUrl = $lastAction->url;
                }
            }
        }

        $firstItemStatus = $request->getSession()->get('_viewStatus', 'status_published');

        $response = $this->render(
            '@NetworkingInitCms/Admin/esi_admin_navbar.html.twig',
            [
                'draftPath' => $draftPath,
                'livePath' => $livePath,
                'editPath' => $editPath,
                'firstItemStatus' => $firstItemStatus,
                'adminPath' => $lastActionUrl,
            ]
        );

        // set the shared max age - which also marks the response as public
        $response->setSharedMaxAge(10);

        return $response;
    }

    public function getPageHelper(): PageHelper
    {
        return $this->pageHelper;
    }

    public function getSnapshotVisibility(PageSnapshotInterface $page)
    {
        $jsonObject = json_decode(
            (string) $page->getVersionedData(),
            null,
            512,
            JSON_THROW_ON_ERROR
        );

        return $jsonObject->visibility;
    }

    /**
     * @throws \Exception
     */
    public function isSnapshotActive(PageSnapshotInterface $page): bool
    {
        $jsonObject = json_decode(
            (string) $page->getVersionedData(),
            null,
            512,
            JSON_THROW_ON_ERROR
        );

        $now = new \DateTime();

        if ($now->getTimestamp() >= $this->getActiveStart($jsonObject)
                ->getTimestamp()
            && $now->getTimestamp() <= $this->getActiveEnd($jsonObject)
                ->getTimestamp()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @throws \Exception
     */
    public function getActiveStart($page): \DateTime
    {
        if (!property_exists($page, 'active_from')
            || null === $page->active_from
        ) {
            return new \DateTime();
        }

        return new \DateTime($page->active_from);
    }

    /**
     * @throws \Exception
     */
    public function getActiveEnd($page): \DateTime
    {
        if (!property_exists($page, 'active_to') || null === $page->active_to) {
            return new \DateTime('+1 day');
        }

        return new \DateTime($page->active_to);
    }

    public function getJsonUrlsAction(Request $request): JsonResponse
    {
        $locale = $request->get('_locale', false);

        $locale = str_replace('-', '_', (string) $locale);
        /** @var PageAdmin $pageAdmin */
        $pageAdmin = $this->pool->getAdminByAdminCode(
            'networking_init_cms.admin.page'
        );

        $pageAdmin->setRequest($request);

        $qb = $pageAdmin->createQuery();

        $alias = $qb->getRootAliases();

        $pageAdmin->getByLocale($qb, $alias[0], 'locale', ['value' => $locale]);

        $qb->setSortBy([], ['fieldName' => 'path']);

        /** @var PageInterface[] $result */
        $result = $qb->execute();

        $setup = [
            'pages' => [],
            'locales' => [],
        ];

        foreach ($result as $page) {
            $setup['pages'][] = [
                'name' => $page->getAdminTitle(),
                'url' => $page->getRoute()->getPath(),
            ];
        }

        $languages = $this->getParameter('networking_init_cms.page.languages');

        foreach ($languages as $language) {
            $setup['locales'][] = [
                $language['label'],
                str_replace(
                    '_',
                    '-',
                    (string) $language['locale']
                ),
            ];
        }

        return new JsonResponse(
            $setup,
            200,
            [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
            ]
        );
    }
}
