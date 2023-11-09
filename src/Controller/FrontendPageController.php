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
use Networking\InitCmsBundle\Entity\PageSnapshot;
use Networking\InitCmsBundle\Helper\LanguageSwitcherHelper;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Networking\InitCmsBundle\Model\PageSnapshotInterface;
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
    protected PageCacheInterface $pageCache;

    protected TokenStorageInterface $tokenStorage;

    protected AuthorizationCheckerInterface $authorizationChecker;

    protected Pool $pool;

    protected LanguageSwitcherHelper $languageSwitcherHelper;

    protected PageManagerInterface $pageManager;

    protected PageHelper $pageHelper;

    /**
     * FrontendPageController constructor.
     */
    public function __construct(
        PageCacheInterface $pageCache,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        Pool $pool,
        LanguageSwitcherHelper $languageSwitcherHelper,
        PageManagerInterface $pageManager,
        PageHelper $pageHelper
    ) {
        $this->pageCache = $pageCache;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->pool = $pool;
        $this->languageSwitcherHelper = $languageSwitcherHelper;
        $this->pageManager = $pageManager;
        $this->pageHelper = $pageHelper;
    }

    protected function getAdminPool(): bool|\Sonata\AdminBundle\Admin\Pool
    {
        if ($this->tokenStorage->getToken() && $this->authorizationChecker->isGranted(
            'ROLE_SONATA_ADMIN'
        )
        ) {
            return $this->pool;
        }

        return false;
    }

    public function indexAction(Request $request)
    {
        return $this->index($request);
    }

    /**
     * @return array|bool|mixed|string|RedirectResponse|Response|null
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function index(Request $request)
    {
        /** @var PageSnapshotInterface $page */
        $page = $request->get('_content');

        $template = $request->get('_template');
        if ($template instanceof Template) {
            $template = $template->template;
        }
        $user = $this->getUser();

        if ($this->pageCache->isCacheable($request, $user) && $page instanceof PageSnapshotInterface) {
            if (!$this->isSnapshotActive($page)) {
                throw new NotFoundHttpException();
            }

            if (PageInterface::VISIBILITY_PUBLIC != $this->getSnapshotVisibility($page)) {
                if (false === $this->authorizationChecker->isGranted('ROLE_USER')) {
                    throw new AccessDeniedException();
                }
            }

            $updatedAt = $this->pageCache->get(sprintf('page_%s_created_at', $page->getId()));
            $cacheKey = 'page_'.$request->getLocale().str_replace('/', '', $request->getPathInfo());

            if ($updatedAt != $page->getSnapshotDate()) {
                $this->pageCache->delete($cacheKey);
            }

            $response = $this->pageCache->get($cacheKey);

            if (!$response instanceof Response) {
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

                $this->pageCache->set($cacheKey, $response);
                $this->pageCache->set(sprintf('page_%s_created_at', $page->getId()), $page->getSnapshotDate());
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
        if ($this->getPageHelper()->isAllowLocaleCookie() && !$this->getPageHelper()->isSingleLanguage()) {
            $cookies = $this->pageHelper->setLocaleCookies($locale);
            foreach ($cookies as $cookie) {
                $response->headers->setCookie($cookie);
            }
        }
    }

    /**
     * Check if page is an alias for another page.
     */
    public function getRedirect(Request $request, PageInterface $page): bool|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        if (method_exists($page, 'getAliasFullPath')) {
            if ($page->getAliasFullPath()) {
                $baseUrl = $request->getBaseUrl();

                return new RedirectResponse($baseUrl.$page->getAliasFullPath());
            }
        }

        return false;
    }

    public function getPageParameters(Request $request): array|bool|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $page = $request->get('_content', null);

        if (is_null($page)) {
            throw $this->createNotFoundException('no page object found');
        }

        if ($page instanceof PageSnapshot) {
            return $this->getLiveParameters($request, $page);
        }

        return $this->getDraftParameters($request, $page);
    }

    public function getDraftParameters(Request $request, PageInterface $page): array|bool|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        if ($redirect = $this->getRedirect($request, $page)) {
            return $redirect;
        }

        if (PageInterface::VISIBILITY_PUBLIC != $page->getVisibility()) {
            if (false === $this->authorizationChecker->isGranted('ROLE_USER')) {
                throw new AccessDeniedException();
            }
        }

        if (PageInterface::STATUS_PUBLISHED != $page->getStatus()) {
            if (!$this->tokenStorage->getToken() || false === $this->authorizationChecker->isGranted(
                'ROLE_SONATA_ADMIN'
            )
            ) {
                $message = 'The requested page has the status "'.$page->getStatus().'", please login to view page';
                throw $this->createNotFoundException($message);
            }
        }

        return ['page' => $page, 'admin_pool' => $this->getAdminPool()];
    }

    public function getLiveParameters(Request $request, PageSnapshot $pageSnapshot)
    {
        /** @var $page PageInterface */
        $page = $this->getPageHelper()->unserializePageSnapshotData($pageSnapshot, false);
        if (!$page->isActive()) {
            throw new NotFoundHttpException('page status '.$page->getStatus());
        }

        if ($redirect = $this->getRedirect($request, $page)) {
            return $redirect;
        }

        if (PageInterface::VISIBILITY_PUBLIC != $page->getVisibility()) {
            if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_USER')) {
                throw new AccessDeniedException();
            }
        }

        return ['page' => $page, 'admin_pool' => $this->getAdminPool()];
    }

    /**
     * Redirect to the admin dashboard.
     *
     * @return RedirectResponse
     */
    public function adminAction()
    {
        $url = $this->generateUrl('sonata_admin_dashboard');

        return $this->redirect($url);
    }

    /**
     * Show the home page (start page) for given locale.
     *
     * @return Response
     */
    public function home(Request $request)
    {
        if ('networking_init_cms_default' === $request->get('_route')) {
            $request = $this->getPageHelper()->matchContentRouteRequest($request);
        }

        return $this->index($request);
    }

    /**
     * Show the home page (start page) for given locale.
     *
     * @return Response
     */
    public function homeAction(Request $request)
    {
        if ('networking_init_cms_default' === $request->get('_route')) {
            $request = $this->getPageHelper()->matchContentRouteRequest($request);
        }

        return $this->index($request);
    }

    /**
     * Change the language in the admin area (currently not implemented in the template).
     */
    public function changeAdminLanguageAction(Request $request, $locale): RedirectResponse
    {
        $request->getSession()->set('admin/_locale', $locale);

        return new RedirectResponse($request->headers->get('referer'));
    }

    /**
     * Change language in the front end area.
     *
     * @return RedirectResponse
     */
    public function changeLanguageAction(Request $request, $oldLocale, $locale)
    {
        $params = [];

        $translationRoute = $this->getTranslationRoute($request->headers->get('referer'), $oldLocale, $locale);

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

        $parts = parse_url($request->headers->get('referer'));

        if (array_key_exists('query', $parts) && $parts['query']) {
            parse_str($parts['query'], $query);

            $params = array_merge($query, $params);
        }

        $newURL = $this->container->get('router')->generate($routeName, $params);

        $response = new RedirectResponse($newURL);

        if ($this->getPageHelper()->isAllowLocaleCookie()) {
            $cookies = $this->pageHelper->setLocaleCookies($locale);
            foreach ($cookies as $cookie) {
                $response->headers->setCookie($cookie);
            }
        }

        return $response;
    }

    /**
     * View the website in Draft mode.
     *
     * @param string $locale
     * @param string /null $path
     *
     * @return RedirectResponse
     */
    public function viewDraftAction(Request $request, $locale = null, $path = null)
    {
        if ($locale) {
            $request->setLocale($locale);
        }

        return $this->changeViewMode($request, PageInterface::STATUS_DRAFT, $path);
    }

    /**
     * View the website in Live mode.
     *
     * @param string $locale
     * @param string /null $path
     *
     * @return RedirectResponse
     */
    public function viewLiveAction(Request $request, $locale = null, $path = null)
    {
        if ($locale) {
            $request->setLocale($locale);
        }

        return $this->changeViewMode($request, PageInterface::STATUS_PUBLISHED, $path);
    }

    /**
     * Change the page viewing mode to live or draft.
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException
     */
    protected function changeViewMode(Request $request, $status, $path)
    {
        if (false === $this->authorizationChecker->isGranted('ROLE_SONATA_ADMIN')) {
            $message = 'Please login to carry out this action';
            throw new AccessDeniedException($message);
        }

        $request->getSession()->set('_viewStatus', $status);

        if ($path) {
            $url = base64_decode((string) $path);
        } else {
            $url = $this->container->get('router')->generate('networking_init_cms_default');
        }

        $response = $this->redirect($url);

        if ($this->getPageHelper()->isAllowLocaleCookie()) {
            $cookies = $this->pageHelper->setLocaleCookies($request->getLocale());
            foreach ($cookies as $cookie) {
                $response->headers->setCookie($cookie);
            }
        }

        return $response;
    }

    /**
     * get the route for the translation of a given page, the referrer page.
     */
    protected function getTranslationRoute($referrer, $oldLocale, $locale): array|\Symfony\Cmf\Component\Routing\RouteObjectInterface|string
    {
        $oldURL = $this->languageSwitcherHelper->getPathInfo($referrer);


        return $this->languageSwitcherHelper->getTranslationRoute($oldURL, $oldLocale, $locale);
    }

    /**
     * @return Response
     */
    public function translationNotFoundAction(Request $request)
    {
        $params = [
            'language' => \Locale::getDisplayLanguage($request->getLocale()),
            'admin_pool' => $this->getAdminPool(),
        ];

        return $this->render($this->getParameter('networking_init_cms.no_translation_template'), $params);
    }

    /**
     * @return Response
     */
    public function pageNotFoundAction()
    {
        $params = ['admin_pool' => $this->getAdminPool()];

        return $this->render($this->getParameter('networking_init_cms.404_template'), $params);
    }

    /**
     * @param null $page_id
     *
     * @return Response
     */
    public function adminNavbarAction(Request $request, $page_id = null)
    {
        if ($page_id) {
            $page = $this->pageManager->find($page_id);
            $request->attributes->set('_content', $page);
        }
        $response = $this->render(
            '@NetworkingInitCms/Admin/esi_admin_navbar.html.twig',
            ['admin_pool' => $this->getAdminPool()]
        );

        // set the shared max age - which also marks the response as public
        $response->setSharedMaxAge(10);

        return $response;
    }

    /**
     * @return PageHelper
     */
    public function getPageHelper()
    {
        return $this->pageHelper;
    }

    public function getSnapshotVisibility(PageSnapshotInterface $page)
    {
        $jsonObject = json_decode((string) $page->getVersionedData(), null, 512, JSON_THROW_ON_ERROR);

        return $jsonObject->visibility;
    }

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function isSnapshotActive(PageSnapshotInterface $page)
    {
        $jsonObject = json_decode((string) $page->getVersionedData(), null, 512, JSON_THROW_ON_ERROR);

        $now = new \DateTime();

        if ($now->getTimestamp() >= $this->getActiveStart($jsonObject)->getTimestamp()
            && $now->getTimestamp() <= $this->getActiveEnd($jsonObject)->getTimestamp()
        ) {
            return PageInterface::STATUS_PUBLISHED == $jsonObject->status;
        }

        return false;
    }

    /**
     * @return \DateTime
     *
     * @throws \Exception
     */
    public function getActiveStart($page)
    {
        if (!property_exists($page, 'active_from')) {
            return new \DateTime();
        }

        return new \DateTime($page->active_from);
    }

    /**
     * @return \DateTime
     *
     * @throws \Exception
     */
    public function getActiveEnd($page)
    {
        if (!property_exists($page, 'active_to')) {
            return new \DateTime();
        }

        return new \DateTime($page->active_to);
    }

    /**
     * @return JsonResponse
     */
    public function getJsonUrlsAction(Request $request)
    {
        $locale = $request->get('_locale', false);

        $locale = str_replace('-', '_', (string) $locale);
        /** @var PageAdmin $pageAdmin */
        $pageAdmin = $this->pool->getAdminByAdminCode('networking_init_cms.admin.page');

        $pageAdmin->setRequest($request);

        $qb = $pageAdmin->createQuery();

        $pageAdmin->getByLocale($qb, 'p', 'locale', ['value' => $locale]);

        $qb->setSortBy([], ['fieldName' => 'path']);

        $result = $qb->execute();

        $setup = [
            'pages' => [],
            'locales' => [],
        ];

        /** @var Page $page */
        foreach ($result as $page) {
            $setup['pages'][] = [
                'name' => $page->getAdminTitle(),
                'url' => $page->getRoute()->getPath(),
            ]
            ;
        }

        $languages = $this->getParameter('networking_init_cms.page.languages');

        foreach ($languages as $language) {
            $setup['locales'][] = [$language['label'], str_replace('_', '-', (string) $language['locale'])];
        }

        return new JsonResponse($setup, 200, ['Content-Type' => 'application/json', 'Access-Control-Allow-Origin' => '*']);
    }
}
