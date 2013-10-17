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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Networking\InitCmsBundle\Entity\BasePage as Page;
use Networking\InitCmsBundle\Entity\PageSnapshot;
use Networking\InitCmsBundle\Helper\LanguageSwitcherHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Networking\InitCmsBundle\Entity\HelpTextRepository;


/**
 * @author net working AG <info@networking.ch>
 */
class FrontendPageController extends Controller
{

    protected function getAdminPool()
    {
        if ($this->get('security.context')->isGranted('ROLE_SONATA_ADMIN')) {
            return $this->get('sonata.admin.pool');
        }

        return false;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function indexAction(Request $request)
    {
        /** @var $page Page */
        $page = $request->get('_content');

        if($page instanceof PageSnapshot){
            return $this->liveAction($request);
        }

        if(!$page){
            throw $this->createNotFoundException('no page object found');
        }

        if ($page->getVisibility() != Page::VISIBILITY_PUBLIC) {
            if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
                throw new AccessDeniedException();
            }
        }

        if ($page->getStatus() != Page::STATUS_PUBLISHED) {
            if (false === $this->get('security.context')->isGranted('ROLE_SONATA_ADMIN')) {
                $message = 'The requested page has the status "' . $page->getStatus() . '", please login to view page';
                throw $this->createNotFoundException($message);
            }
        }

        return array('page' => $page, 'admin_pool' => $this->getAdminPool());
    }


    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function liveAction(Request $request)
    {
        /** @var $page PageSnapshot */
        $pageSnapshot = $request->get('_content');

        /** @var $page Page */
        $page = $this->get('serializer')->deserialize(
            $pageSnapshot->getVersionedData(),
            'Networking\InitCmsBundle\Entity\Page',
            'json'
        );


        if ($page->getVisibility() != Page::VISIBILITY_PUBLIC) {
            if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
                throw new AccessDeniedException();
            }
        }

        return array('page' => $page, 'admin_pool' => $this->getAdminPool());
    }

    /**
     * @Route("/admin/", name="networking_init_cms_admin")
     */
    public function adminAction()
    {
        $url = $this->generateUrl('sonata_admin_dashboard');

        return $this->redirect($url);
    }

    /**
     * @Route("/", name="networking_init_cms_home")
     *
     * @return array
     */
    public function homeAction()
    {
        $repository = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:Page');

        $page = $repository->findOneBy(array('isHome' => true, 'locale' => $this->getRequest()->getLocale()));

        return array('page' => $page);
    }

    /**
     * @param Request $request
     * @param $locale
     *
     * @Route("/change_admin_language/{locale}", name="change_admin_language", requirements={"locale" = ".+"})
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeAdminLanguageAction(Request $request, $locale)
    {
        $request->getSession()->set('admin/_locale', $locale);

        return new RedirectResponse($request->headers->get('referer'));
    }

    /**
     * @param Request $request
     * @param $locale
     *
     * @Route("/change_language/{locale}", name="networking_init_change_language", requirements={"locale" = ".+"})
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeLanguageAction(Request $request, $locale)
    {
        $params = array();

        $translationRoute = $this->getTranslationRoute($request->headers->get('referer'), $locale);

        $request->getSession()->set('_locale', $locale);

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

        return new RedirectResponse($newURL);

    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $locale
     * @param string/null $path
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function viewDraftAction(Request $request, $locale, $path = null)
    {
        $request->getSession()->set('_locale', $locale);

        return $this->changePageStatus($request, Page::STATUS_DRAFT, $path);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $locale
     * @param string/null $path
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function viewLiveAction(Request $request, $locale, $path = null)
    {
        $request->getSession()->set('_locale', $locale);

        return $this->changePageStatus($request, Page::STATUS_PUBLISHED, $path);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $status
     * @param $path
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function changePageStatus(Request $request, $status, $path)
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

        return new RedirectResponse($url);
    }

    /**
     * @param $referer
     * @param $locale
     * @return array|\Symfony\Cmf\Component\Routing\RouteObjectInterface
     */
    protected function getTranslationRoute($referer, $locale)
    {
        /** @var $languageSwitcherHelper LanguageSwitcherHelper */
        $languageSwitcherHelper = $this->get('networking_init_cms.page.helper.language_switcher');

        $oldURL = $languageSwitcherHelper->getPathInfo($referer);

        return $languageSwitcherHelper->getTranslationRoute($oldURL, $locale);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function translationNotFoundAction(Request $request)
    {
        $params = array(
            'admin_pool' => $this->getAdminPool(),
            'language' => \Locale::getDisplayLanguage($this->getRequest()->getLocale())
        );

        return $this->render($this->container->getParameter('networking_init_cms.no_translation_template'), $params);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pageNotFoundAction(Request $request)
    {
        $params = array('admin_pool' => $this->getAdminPool());
        return $this->render($this->container->getParameter('networking_init_cms.404_template'), $params);
    }

}
