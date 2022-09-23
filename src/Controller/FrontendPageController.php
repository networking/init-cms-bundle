<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Controller;

use Networking\InitCmsBundle\Admin\Model\PageAdmin;
use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Model\ContentRouteManager;
use Networking\InitCmsBundle\Model\Page;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageSnapshot;
use Networking\InitCmsBundle\Model\PageSnapshotInterface;
use Networking\InitCmsBundle\Helper\LanguageSwitcherHelper;

/**
 * Class FrontendPageController.
 *
 * @author net working AG <info@networking.ch>
 */
class FrontendPageController extends AbstractController
{
    /**
     * @var PageCacheInterface
     */
    protected $pageCache;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var LanguageSwitcherHelper
     */
    protected $languageSwitcherHelper;

    /**
     * @var PageManagerInterface
     */
    protected $pageManager;

    /**
     * @var PageHelper
     */
    protected $pageHelper;

    /**
     * FrontendPageController constructor.
     * @param PageCacheInterface $pageCache
     * @param TokenStorageInterface $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param Pool $pool
     * @param LanguageSwitcherHelper $languageSwitcherHelper
     * @param PageManagerInterface $pageManager
     * @param PageHelper $pageHelper
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

    /**
     * @return bool|\Sonata\AdminBundle\Admin\Pool
     */
    protected function getAdminPool()
    {
        if ($this->tokenStorage->getToken() && $this->authorizationChecker->isGranted(
                'ROLE_SONATA_ADMIN'
            )
        ) {
            return $this->pool;
        }

        return false;
    }

    public function indexAction(Request $request){
        return $this->index($request);
    }

    /**
     * @param Request $request
     * @return array|bool|mixed|string|RedirectResponse|Response|null
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function index(Request $request)
    {
        /** @var PageSnapshotInterface $page */
        $page = $request->get('_content');

        $template = $request->get('_template');
        if ($template instanceof \Sensio\Bundle\FrameworkExtraBundle\Configuration\Template) {
            $template = $template->getTemplate();
        }
        $user = $this->getUser();

        if ($this->pageCache->isCacheable($request, $user) && $page instanceof PageSnapshotInterface) {
            if (!$this->isSnapshotActive($page)) {
                throw new NotFoundHttpException();
            }

            if ($this->getSnapshotVisibility($page) != PageInterface::VISIBILITY_PUBLIC) {
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

            if (!$response || !$response instanceof Response) {
                $params = $this->getPageParameters($request);

                if ($params instanceof RedirectResponse) {
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
                return $params;
            }
            $html = $this->renderView(
                $template,
                $params
            );
            $response = new Response($html);
        }

        if ($this->getPageHelper()->isAllowLocaleCookie() && !$this->getPageHelper()->isSingleLanguage()) {

            $cookies = $this->pageHelper->setLocaleCookies($request->getLocale());
            foreach ($cookies as $cookie){
                $response->headers->setCookie($cookie);
            }
        }

        return $response;
    }

    /**
     * Check if page is an alias for another page.
     *
     * @param Request $request
     * @param PageInterface $page
     *
     * @return bool|RedirectResponse
     */
    public function getRedirect(Request $request, PageInterface $page)
    {
        if (method_exists($page, 'getAlias') && $page instanceof PageInterface) {
            if ($alias = $page->getAlias()) {
                $alias->getFullPath();
                $baseUrl = $request->getBaseUrl();

                $route = ContentRouteManager::generateRoute($alias->getContentRoute(), $alias->getFullPath(), '');

                return new RedirectResponse($baseUrl.$route->getPath());
            }
        }

        return false;
    }

    /**
     * @param Request $request
     *
     * @return array|bool|RedirectResponse
     */
    public function getPageParameters(Request $request)
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

    /**
     * @param Request $request
     * @param PageInterface $page
     *
     * @return array|bool|RedirectResponse
     */
    public function getDraftParameters(Request $request, PageInterface $page)
    {
        if ($redirect = $this->getRedirect($request, $page)) {
            return $redirect;
        }

        if ($page->getVisibility() != PageInterface::VISIBILITY_PUBLIC) {
            if (false === $this->authorizationChecker->isGranted('ROLE_USER')) {
                throw new AccessDeniedException();
            }
        }

        if ($page->getStatus() != PageInterface::STATUS_PUBLISHED) {
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

        if ($page->getVisibility() != PageInterface::VISIBILITY_PUBLIC) {
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
     * @param Request $request
     *
     * @return Response
     */
    public function homeAction(Request $request)
    {
        if ($request->get('_route') === 'networking_init_cms_default') {
            $request = $this->getPageHelper()->matchContentRouteRequest($request);
        }

        return $this->indexAction($request);
    }

    /**
     * Change the language in the admin area (currently not implemented in the template).
     *
     * @param Request $request
     * @param $locale
     *
     * @return RedirectResponse
     */
    public function changeAdminLanguageAction(Request $request, $locale)
    {
        $request->getSession()->set('admin/_locale', $locale);

        return new RedirectResponse($request->headers->get('referer'));
    }

    /**
     * Change language in the front end area.
     *
     * @param Request $request
     * @param $oldLocale
     * @param $locale
     *
     * @return RedirectResponse
     */
    public function changeLanguageAction(Request $request, $oldLocale, $locale)
    {
        $params = [];

        $translationRoute = $this->getTranslationRoute($request->headers->get('referer'), $oldLocale, $locale);

        $request->setLocale($locale);

        if (!is_array($translationRoute)) {
            $routeName = $translationRoute;
        } else {
            $routeName = $translationRoute['_route'];
            unset($translationRoute['_route']);
            foreach ($translationRoute as $key => $var) {
                $params[$key] = $var;
            }
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
            foreach ($cookies as $cookie){
                $response->headers->setCookie($cookie);
            }
        }

        return $response;
    }

    /**
     * View the website in Draft mode.
     *
     * @param Request $request
     * @param string $locale
     * @param string /null $path
     *
     * @return RedirectResponse
     */
    public function viewDraftAction(Request $request, $locale = null, $path = null)
    {

        if($locale){
            $request->setLocale($locale);
        }

        return $this->changeViewMode($request, PageInterface::STATUS_DRAFT, $path);
    }

    /**
     * View the website in Live mode.
     *
     * @param Request $request
     * @param string $locale
     * @param string /null $path
     *
     * @return RedirectResponse
     */
    public function viewLiveAction(Request $request, $locale = null, $path = null)
    {
        if($locale){
            $request->setLocale($locale);
        }

        return $this->changeViewMode($request, PageInterface::STATUS_PUBLISHED, $path);
    }

    /**
     * Change the page viewing mode to live or draft.
     *
     * @param Request $request
     * @param $status
     * @param $path
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
            $url = base64_decode($path);
        } else {
            $url = $this->container->get('router')->generate('networking_init_cms_default');
        }

        $response = $this->redirect($url);
        if ($this->getPageHelper()->isAllowLocaleCookie()) {
            $cookies = $this->pageHelper->setLocaleCookies($request->getLocale());
            foreach ($cookies as $cookie){
                $response->headers->setCookie($cookie);
            }
        }

        return $response;
    }

    /**
     * get the route for the translation of a given page, the referrer page.
     *
     * @param $referrer
     * @param $oldLocale
     * @param $locale
     *
     * @return array|RouteObjectInterface
     */
    protected function getTranslationRoute($referrer, $oldLocale, $locale)
    {

        $oldURL = $this->languageSwitcherHelper->getPathInfo($referrer);

        return $this->languageSwitcherHelper->getTranslationRoute($oldURL, $oldLocale, $locale);
    }

    /**
     * @param Request $request
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
     * @param Request $request
     * @param null $page_id
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

    /**
     * @param PageSnapshotInterface $page
     *
     * @return mixed
     */
    public function getSnapshotVisibility(PageSnapshotInterface $page)
    {
        $jsonObject = json_decode($page->getVersionedData());

        return $jsonObject->visibility;
    }

    /**
     * @param PageSnapshotInterface $page
     * @return bool
     * @throws \Exception
     */
    public function isSnapshotActive(PageSnapshotInterface $page)
    {
        $jsonObject = json_decode($page->getVersionedData());

        $now = new \DateTime();

        if ($now->getTimestamp() >= $this->getActiveStart($jsonObject)->getTimestamp() &&
            $now->getTimestamp() <= $this->getActiveEnd($jsonObject)->getTimestamp()
        ) {
            return $jsonObject->status == PageInterface::STATUS_PUBLISHED;
        }

        return false;
    }

    /**
     * @param $page
     * @return \DateTime
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
     * @param $page
     * @return \DateTime
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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getJsonUrlsAction(Request $request)
    {
        $locale = $request->get('_locale', false);

        $locale = str_replace('-', '_', $locale);
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

            $setup['pages'][$page->getAdminTitle()] = $page->getRoute()->getPath();
        }

        $languages = $this->getParameter('networking_init_cms.page.languages');


        foreach ($languages as $language) {
            $setup['locales'][] = [$language['label'], str_replace('_', '-', $language['locale'])];
        }

        return new JsonResponse($setup);
    }
}
