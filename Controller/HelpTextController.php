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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class HelpTextController
 * @package Networking\InitCmsBundle\Controller
 *
 * @author net working AG <info@networking.ch>
 */
class HelpTextController extends Controller
{


    /**
     * Help text page action
     *
     * @param Request $request
     * @param $adminCode
     * @param string $action
     * @return array
     */
    public function adminHelpAction(Request $request, $adminCode, $action = '')
    {
        $parameters = array('adminCode' => $adminCode, 'action' => $action);
        $defaultAdminCode = array('dashboard', 'overview');

        if ($action == '') {
            $translationKey = $adminCode;
        } else {
            $translationKey = $adminCode . '.' . $action;
        }

        $helpTextManager = $this->get('networking_init_cms.help_text_manager');
        $helpText = $helpTextManager->getHelpTextByKeyLocale($translationKey, $request->getLocale());

        $parameters['help_text'] = $helpText;
        if (!in_array($adminCode, $defaultAdminCode)) {

            $admin = $this->container->get('sonata.admin.pool')->getAdminByAdminCode($adminCode);
            $admin->setRequest($request);
            $parameters['admin'] = $admin;

        }

        /** @var \Networking\InitCmsBundle\Admin\Pool $pool */
        $pool = $this->get('sonata.admin.pool');
        $parameters['admin_pool'] = $pool;
        $dashBoardGroups = $pool->getDashboardNavigationGroups();


        $parameters['help_nav'] = $this->adminGetHelpTextNavigation(
            $dashBoardGroups,
            $request->getLocale(),
            $helpTextManager
        );

        return $parameters;
    }

    /**
     * Create the navigation for the help text view
     *
     * @param array $dashBoardGroups
     * @param $locale
     * @param $helpTextManager
     * @return array
     */
    protected function adminGetHelpTextNavigation(array $dashBoardGroups, $locale, $helpTextManager)
    {
        $navArray = array();

        //add overview & dashboard manually
        $navArray['overview']['group_name'] = 'Help';
        $navArray['overview']['group_items']['0']['adminCode'] = 'overview';
        $navArray['overview']['group_items']['0']['action'] = '';
        $navArray['overview']['group_items']['0']['title'] = $this->get('translator')->trans(
            'overview.title',
            array(),
            'HelpTextAdmin'
        );

        $navArray['dashboard']['group_name'] = 'Dashboard';
        $navArray['dashboard']['group_items']['0']['adminCode'] = 'dashboard';
        $navArray['dashboard']['group_items']['0']['action'] = '';
        $navArray['dashboard']['group_items']['0']['title'] = $this->get('translator')->trans(
            'dashboard.title',
            array(),
            'HelpTextAdmin'
        );

        foreach ($dashBoardGroups as $key => $group) {
            foreach ($group['sub_group'] as $subGroup) {


                $navArray[$subGroup['label']]['group_items'] = array();
                $i = 0;
                foreach ($subGroup['items'] as $admin) {
                    if (0 == $i++) {
                        $navArray[$subGroup['label']]['group_name'] = $this->get('translator')->trans(
                            $subGroup['label'],
                            array(),
                            $admin->getTranslationDomain()
                        );
                    }

                    $help_text_result = $helpTextManager->searchHelpTextByKeyLocale($admin->getCode(), $locale);
                    if (count($help_text_result) > 0) {

                        foreach ($help_text_result as $row) {
                            //split Translation Key into adminCode and action
                            $strripos = strripos($row->getTranslationKey(), '.');
                            $action = substr($row->getTranslationKey(), $strripos + 1);
                            $navArray[$subGroup['label']]['group_items'][$row->getId()]['adminCode'] = $admin->getCode(
                            );
                            $navArray[$subGroup['label']]['group_items'][$row->getId()]['action'] = $action;
                            $navArray[$subGroup['label']]['group_items'][$row->getId()]['title'] = $row->getTitle();
                        }
                    }
                }
            }
        }

        return $navArray;
    }
}
