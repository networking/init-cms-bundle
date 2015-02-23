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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
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
 * Class FrontendPageController
 * @package Networking\InitCmsBundle\Controller
 * @author net working AG <info@networking.ch>
 */
class FrontendPageController extends Controller
{

    /**
     * @return bool|\Sonata\AdminBundle\Admin\Pool
     */
    protected function getAdminPool()
    {
        if ($this->get('security.context')->getToken() && $this->get('security.context')->isGranted(
                'ROLE_SONATA_ADMIN'
            )
        ) {

            return $this->get('sonata.admin.pool');
        }

        return false;
    }


    /**
     * Render the page
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {

        /** @var \Networking\InitCmsBundle\Lib\PhpCacheInterface $phpCache */
        $phpCache = $this->get('networking_init_cms.lib.php_cache');

        /** @var PageSnapshotInterface $page */
        $page = $request->get('_content');

        if($phpCache->isCacheable($request, $this->getUser()) && $page instanceof PageSnapshotInterface){

            $updatedAt = $phpCache->get(sprintf('page_%s_created_at', $page->getId()));
            $cacheKey = $request->getLocale().$request->getPathInfo();

            if($updatedAt != $page->getSnapshotDate()){
                $phpCache->delete($cacheKey);
            }

            if(!$response = $phpCache->get($request->getLocale().$request->getPathInfo())){
                $params = $this->getPageParameters($request);

                if($params instanceof RedirectResponse){
                    return $params;
                }
                $html = $this->renderView(
                    $request->get('_template'),
                    $params
                );
                $response =  new Response($html);
                if (!$request->cookies || $request->cookies->get('_locale')) {
                    $response->headers->setCookie(new Cookie('_locale', $request->getLocale()));
                }

                $phpCache->set($cacheKey, $response);
                $phpCache->set(sprintf('page_%s_created_at', $page->getId()), $page->getSnapshotDate());
            }else{
                $phpCache->touch($cacheKey);
                $phpCache->touch(sprintf('page_%s_created_at', $page->getId()));
            }

        }else{
            $params = $this->getPageParameters($request);

            if($params instanceof RedirectResponse){
                return $params;
            }
            $html = $this->renderView(
                $request->get('_template'),
                $params
            );
            $response =  new Response($html);
            if (!$request->cookies || $request->cookies->get('_locale')) {
                $response->headers->setCookie(new Cookie('_locale', $request->getLocale()));
            }
        }

        return $response;
    }


    /**
     * Render the Live version of a page
     *
     * @deprecated will be removed in version 3.0
     *
     * @param Request $request
     * @return Response
     */
    public function liveAction(Request $request)
    {

        return $this->indexAction($request);
    }

    /**
     * Check if page is an alias for another page
     *
     * @param Request $request
     * @param PageInterface $page
     * @return bool|RedirectResponse
     */
    public function getRedirect(Request $request, PageInterface $page)
    {
        if (method_exists($page, 'getAlias') && $page instanceof PageInterface) {
            if ($alias = $page->getAlias()) {
                $alias->getFullPath();
                $baseUrl = $request->getBaseUrl();

                return new RedirectResponse($baseUrl . $alias->getFullPath());
            }
        }

        return false;
    }

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

    public function getDraftParameters(Request $request, PageInterface $page)
    {

        if($redirect = $this->getRedirect($request, $page)){
            return $redirect;
        }

        if ($page->getVisibility() != PageInterface::VISIBILITY_PUBLIC) {
            if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
                throw new AccessDeniedException();
            }
        }

        if ($page->getStatus() != PageInterface::STATUS_PUBLISHED) {
            if (!$this->get('security.context')->getToken() || false === $this->get('security.context')->isGranted(
                    'ROLE_SONATA_ADMIN'
                )
            ) {
                $message = 'The requested page has the status "' . $page->getStatus() . '", please login to view page';
                throw $this->createNotFoundException($message);
            }
        }

        return array('page' => $page, 'admin_pool' => $this->getAdminPool());
    }

    public function getLiveParameters(Request $request, PageSnapshot $pageSnapshot)
    {

        /** @var $page PageInterface */
        $page = $this->getPageHelper()->unserializePageSnapshotData($pageSnapshot, false);

        if (!$page->isActive()) {
            throw new NotFoundHttpException();
        }

        if($redirect = $this->getRedirect($request, $page)){
            return $redirect;
        }

        if ($page->getVisibility() != PageInterface::VISIBILITY_PUBLIC) {

            if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
                throw new AccessDeniedException();
            }
        }

        return array('page' => $page, 'admin_pool' => $this->getAdminPool());
    }

    /**
     * Redirect to the admin dashboard
     *
     * @return RedirectResponse
     */
    public function adminAction()
    {
        $url = $this->generateUrl('sonata_admin_dashboard');

        return $this->redirect($url);
    }

    /**
     * Show the home page (start page) for given locale
     *
     * @param Request $request
     *
     * @return array
     */
    public function homeAction(Request $request)
    {
        if ($request->get('_route') === 'networking_init_cms_default') {
            $request = $this->getPageHelper()->matchContentRouteRequest($request);
        }

        return $this->indexAction($request);
    }

    /**
     * Change the language in the admin area (currently not implemented in the template)
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
     * Change language in the front end area
     *
     * @param Request $request
     * @param $locale
     *
     * @return RedirectResponse
     */
    public function changeLanguageAction(Request $request, $locale)
    {
        $params = array();

        $translationRoute = $this->getTranslationRoute($request->headers->get('referer'), $locale);

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

        $parts = parse_url($request->headers->get('referer'));

        if (array_key_exists('query', $parts) && $parts['query']) {
            parse_str($parts['query'], $query);

            $params = array_merge($query, $params);
        }

        $newURL = $this->get('router')->generate($routeName, $params);

        $response = new RedirectResponse($newURL);
        $response->headers->setCookie(new Cookie('_locale', $locale));

        return $response;
    }

    /**
     * View the website in Draft mode
     *
     * @param Request $request
     * @param string $locale
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
     * View the website in Live mode
     *
     * @param Request $request
     * @param string $locale
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
     * Change the page viewing mode to live or draft
     *
     * @param Request $request
     * @param $status
     * @param $path
     *
     * @return RedirectResponse
     * @throws AccessDeniedException
     */
    protected function changeViewMode(Request $request, $status, $path)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_SONATA_ADMIN')) {
            $message = 'Please login to carry out this action';
            throw new AccessDeniedException($message);
        }

        $request->getSession()->set('_viewStatus', $status);

        if ($path) {
            $url = base64_decode($path);
        } else {
            $url = $this->get('router')->generate('networking_init_cms_default');
        }
        header("location:".$url, null, 302);
        exit;
    }

    /**
     * get the route for the translation of a given page, the referrer page
     *
     * @param $referrer
     * @param $locale
     *
     * @return array|RouteObjectInterface
     */
    protected function getTranslationRoute($referrer, $locale)
    {
        /** @var $languageSwitcherHelper LanguageSwitcherHelper */
        $languageSwitcherHelper = $this->get('networking_init_cms.page.helper.language_switcher');

        $oldURL = $languageSwitcherHelper->getPathInfo($referrer);

        return $languageSwitcherHelper->getTranslationRoute($oldURL, $locale);
    }

    /**
     * @return Response
     */
    public function translationNotFoundAction(Request $request)
    {
        $params = array(
            'language' => \Locale::getDisplayLanguage($request->getLocale()),
            'admin_pool' => $this->getAdminPool()
        );

        return $this->render($this->container->getParameter('networking_init_cms.no_translation_template'), $params);
    }

    /**
     * @return Response
     */
    public function pageNotFoundAction()
    {
        $params = array('admin_pool' => $this->getAdminPool());

        return $this->render($this->container->getParameter('networking_init_cms.404_template'), $params);
    }

    /**
     * Deliver the admin navigation bar via ajax
     *
     * @param null $page_id
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
            'NetworkingInitCmsBundle:Admin:esi_admin_navbar.html.twig',
            array('admin_pool' => $this->getAdminPool())
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

}
