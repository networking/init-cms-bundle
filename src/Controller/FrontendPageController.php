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
use Networking\InitCmsBundle\Model\ContentRouteManager;
use Networking\InitCmsBundle\Model\Page;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
class FrontendPageController extends Controller
{
    /**
     * @return bool|\Sonata\AdminBundle\Admin\Pool
     */
    protected function getAdminPool()
    {
        if ($this->get('security.token_storage')->getToken() && $this->get('security.authorization_checker')->isGranted(
                'ROLE_SONATA_ADMIN'
            )
        ) {
            return $this->get('sonata.admin.pool');
        }

        return false;
    }

    /**
     * Render the page.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {

        /** @var \Networking\InitCmsBundle\Lib\PhpCacheInterface $phpCache */
        $phpCache = $this->get('networking_init_cms.lib.php_cache');

        /** @var PageSnapshotInterface $page */
        $page = $request->get('_content');

        $template = $request->get('_template');
        if ($template instanceof \Sensio\Bundle\FrameworkExtraBundle\Configuration\Template) {
            $template = $template->getTemplate();
        }
        $user = $this->getUser();

        if ($phpCache->isCacheable($request, $user) && $page instanceof PageSnapshotInterface) {
            if (!$this->isSnapshotActive($page)) {
                throw new NotFoundHttpException();
            }

            if ($this->getSnapshotVisibility($page) != PageInterface::VISIBILITY_PUBLIC) {
                if (false === $this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
                    throw new AccessDeniedException();
                }
            }

            $updatedAt = $phpCache->get(sprintf('page_%s_created_at', $page->getId()));
            $cacheKey = $request->getLocale().$request->getPathInfo();

            if ($updatedAt != $page->getSnapshotDate()) {
                $phpCache->delete($cacheKey);
            }

            $response = $phpCache->get($request->getLocale().$request->getPathInfo());

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

                $phpCache->set($cacheKey, $response);
                $phpCache->set(sprintf('page_%s_created_at', $page->getId()), $page->getSnapshotDate());
            } else {
                $phpCache->touch($cacheKey);
                $phpCache->touch(sprintf('page_%s_created_at', $page->getId()));
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
            $response->headers->setCookie(new Cookie('_locale', $request->getLocale()));
        }

        return $response;
    }

    /**
     * Check if page is an alias for another page.
     *
     * @param Request       $request
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
        /** @var $page \Networking\InitCmsBundle\Model\PageInterface */
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
     * @param Request       $request
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
            if (false === $this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
                throw new AccessDeniedException();
            }
        }

        if ($page->getStatus() != PageInterface::STATUS_PUBLISHED) {
            if (!$this->get('security.token_storage')->getToken() || false === $this->get('security.authorization_checker')->isGranted(
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
            if (false === $this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
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

        $newURL = $this->get('router')->generate($routeName, $params);

        $response = new RedirectResponse($newURL);

        if ($this->getPageHelper()->isAllowLocaleCookie()) {
            $response->headers->setCookie(new Cookie('_locale', $locale));
        }

        return $response;
    }

    /**
     * View the website in Draft mode.
     *
     * @param Request      $request
     * @param string       $locale
     * @param string /null $path
     *
     * @return RedirectResponse
     */
    public function viewDraftAction(Request $request, $locale, $path = null)
    {
        $request->setLocale($locale);

        return $this->changeViewMode($request, PageInterface::STATUS_DRAFT, $path);
    }

    /**
     * View the website in Live mode.
     *
     * @param Request      $request
     * @param string       $locale
     * @param string /null $path
     *
     * @return RedirectResponse
     */
    public function viewLiveAction(Request $request, $locale, $path = null)
    {
        $request->setLocale($locale);

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
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_SONATA_ADMIN')) {
            $message = 'Please login to carry out this action';
            throw new AccessDeniedException($message);
        }

        $request->getSession()->set('_viewStatus', $status);

        if ($path) {
            $url = base64_decode($path);
        } else {
            $url = $this->get('router')->generate('networking_init_cms_default');
        }

        return $this->redirect($url);
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
        /** @var $languageSwitcherHelper LanguageSwitcherHelper */
        $languageSwitcherHelper = $this->get('networking_init_cms.page.helper.language_switcher');

        $oldURL = $languageSwitcherHelper->getPathInfo($referrer);

        return $languageSwitcherHelper->getTranslationRoute($oldURL, $oldLocale, $locale);
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

        return $this->render($this->container->getParameter('networking_init_cms.no_translation_template'), $params);
    }

    /**
     * @return Response
     */
    public function pageNotFoundAction()
    {
        $params = ['admin_pool' => $this->getAdminPool()];

        return $this->render($this->container->getParameter('networking_init_cms.404_template'), $params);
    }

    /**
     * Deliver the admin navigation bar via ajax.
     *
     * @param null $page_id
     *
     * @return Response
     */
    public function adminNavbarAction(Request $request, $page_id = null)
    {
        if ($page_id) {

            /** @var \Networking\InitCmsBundle\Entity\PageManager $pageManager */
            $pageManager = $this->container->get('networking_init_cms.page_manager');
            $page = $pageManager->find($page_id);
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
     * @return \Networking\InitCmsBundle\Helper\PageHelper
     */
    public function getPageHelper()
    {
        return $this->container->get('networking_init_cms.helper.page_helper');
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
     *
     * @return bool
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
     * Get activeFrom.
     *
     * @return \DateTime
     */
    public function getActiveStart($page)
    {
        if (!property_exists($page, 'active_from')) {
            return new \DateTime();
        }

        return new \DateTime($page->active_from);
    }

    /**
     * @return \Datetime
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
		$pageAdmin = $this->get('networking_init_cms.admin.page');

		$pageAdmin->setRequest($request);

		$qb = $pageAdmin->createQuery();

		$pageAdmin->getByLocale($qb, 'p', 'locale', ['value' => $locale]);

		$qb->setSortBy([], ['fieldName' => 'path']);

		$result = $qb->execute();

		$setup = [
			'pages' => [ ],
			'locales' => []
		];


		/** @var Page $page */
		foreach ($result as $page){

			$setup['pages'][$page->getAdminTitle()] = $page->getRoute()->getPath();
		}

		$languages = $this->getParameter('networking_init_cms.page.languages');


		foreach ($languages as $language){
			$setup['locales'][] = [$language['label'], str_replace('_', '-', $language['locale'])];
		}

		return new JsonResponse($setup);
	}
}
