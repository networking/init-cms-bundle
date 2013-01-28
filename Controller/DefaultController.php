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
use Networking\InitCmsBundle\Entity\Page;
use Networking\InitCmsBundle\Entity\PageSnapshot;
use Networking\InitCmsBundle\Helper\LanguageSwitcherHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Networking\InitCmsBundle\Entity\HelpTextRepository;


/**
 * @author net working AG <info@networking.ch>
 */
class DefaultController extends Controller
{


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

        return array('page' => $page);
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

        return array('page' => $page);
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

        $oldLocale = $request->getLocale();

        if (($oldLocale == $locale)) {
            return new RedirectResponse($request->headers->get('referer'));
        }
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function viewDraftAction(Request $request, $path = null)
    {
        return $this->changePageStatus($request, Page::STATUS_DRAFT, $path);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function viewLiveAction(Request $request, $path = null)
    {
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
            throw $this->createNotFoundException($message);
        }

        $request->getSession()->set('_viewStatus', $status);

        if ($path) {
            $url = urldecode($path);
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
     * @param $admin
     * @param $action
     * @return void
     * @Template()
     */
    public function adminHelpAction(Request $request, $adminCode, $action = '')
    {
        $parameters = array();

        if ($action == '') {
            $translationKey = $adminCode;
        } else {
            $translationKey = $adminCode . '.' . $action;
        }
        $repository = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:HelpText');
        $helpText = $repository->getHelpTextByKeyLocale($translationKey, $request->getLocale());
        $parameters['help_text'] = $helpText;
        if ($adminCode != 'dashboard') {

            $admin = $this->container->get('sonata.admin.pool')->getAdminByAdminCode($adminCode);
            $admin->setRequest($request);
            $parameters['admin'] = $admin;

        }

        $parameters['admin_pool'] = $this->get('sonata.admin.pool');
        $dashBoardGroups = $this->container->get('sonata.admin.pool')->getDashboardGroups();

        $parameters['help_nav'] = $this->adminGetHelpTextNavigation(
            $dashBoardGroups,
            $request->getLocale(),
            $repository
        );

        return $parameters;
    }

    protected function adminGetHelpTextNavigation(array $dashBoardGroups, $locale, HelpTextRepository $repository)
    {
        $nav_array = array();

        //add dashboard manually
        $nav_array['dashboard']['group_name'] = 'Dashboard';
        $nav_array['dashboard']['group_items']['0']['adminCode'] = 'dashboard';
        $nav_array['dashboard']['group_items']['0']['action'] = '';
        $nav_array['dashboard']['group_items']['0']['title'] = $this->get('translator')->trans('title_dashboard', array(), 'SonataAdminBundle');

        foreach ($dashBoardGroups as $key => $group) {
            $nav_array[$key]['group_name'] = $group['label'];
            $nav_array[$key]['group_items'] = array();

            foreach ($group['items'] as $admin) {

                $help_text_result = $repository->searchHelpTextByKeyLocale($admin->getCode(), $locale);
                if (count($help_text_result) > 0) {
                    foreach ($help_text_result as $row) {
                        //split Translation Key into adminCode and action
                        $strripos = strripos($row->getTranslationKey(), '.');
                        $adminCode = substr($row->getTranslationKey(), 0, $strripos);
                        $action = substr($row->getTranslationKey(), $strripos + 1);
                        $nav_array[$key]['group_items'][$row->getId()]['adminCode'] = $adminCode;
                        $nav_array[$key]['group_items'][$row->getId()]['action'] = $action;
                        $nav_array[$key]['group_items'][$row->getId()]['title'] = $row->getTitle();
                    }

                }
            }
        }

        return $nav_array;
    }
}
