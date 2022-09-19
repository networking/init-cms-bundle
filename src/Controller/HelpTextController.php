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

use Networking\InitCmsBundle\Model\HelpTextManagerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
/**
 * Class HelpTextController.
 *
 * @author net working AG <info@networking.ch>
 */
class HelpTextController extends AbstractController
{
    /**
     * @var HelpTextManagerInterface
     */
    private $helpTextManager;

    /**
     * @var MutableTemplateRegistryInterface
     */
    private $templateRegistry;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * HelpTextController constructor.
     * @param HelpTextManagerInterface $helpTextManager
     * @param TemplateRegistryInterface $templateRegistry
     * @param Pool $pool
     * @param TranslatorInterface $translator
     */
    public function __construct(
        HelpTextManagerInterface $helpTextManager,
        TemplateRegistryInterface $templateRegistry,
        Pool $pool,
        TranslatorInterface $translator
    ) {
        $this->helpTextManager = $helpTextManager;
        $this->templateRegistry = $templateRegistry;
        $this->pool = $pool;
        $this->translator = $translator;
    }

    /**
     * Help text page action.
     *
     * @param Request $request
     * @param $adminCode
     * @param string $action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adminHelpAction(Request $request, $adminCode, $action = '')
    {
        $parameters = ['adminCode' => $adminCode, 'action' => $action];
        $defaultAdminCode = ['dashboard', 'overview'];

        if ($action == '') {
            $translationKey = $adminCode;
        } else {
            $translationKey = $adminCode.'.'.$action;
        }

        $helpText = $this->helpTextManager->getHelpTextByKeyLocale($translationKey, $request->getLocale());

        $parameters['help_text'] = $helpText;
        if (!in_array($adminCode, $defaultAdminCode)) {
            $admin = $this->pool->getAdminByAdminCode($adminCode);
            $admin->setRequest($request);
            $parameters['admin'] = $admin;
        }

        $parameters['admin_pool'] = $this->pool;
        $parameters['base_template'] = isset($admin) ? $this->getBaseTemplate(
            $request
        ) : '@NetworkingInitCms/admin_layout.html.twig';
        $dashBoardGroups = $this->pool->getDashboardGroups();

        $parameters['help_nav'] = $this->adminGetHelpTextNavigation(
            $dashBoardGroups,
            $request->getLocale(),
            $this->helpTextManager
        );

        return $this->render(
            '@NetworkingInitCms/HelpText/adminHelp.html.twig',
            $parameters
        );
    }

    /**
     * return the base template name.
     *
     * @param Request $request
     *
     * @return string the template name
     */
    protected function getBaseTemplate(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            return $this->templateRegistry->getTemplate('ajax');
        }

        return $this->templateRegistry->getTemplate('layout');
    }

    /**
     * Create the navigation for the help text view.
     *
     * @param array $dashBoardGroups
     * @param $locale
     * @param $helpTextManager
     *
     * @return array
     */
    protected function adminGetHelpTextNavigation(array $dashBoardGroups, $locale, $helpTextManager)
    {
        $navArray = [];

        //add overview & dashboard manually
        $navArray['overview']['group_name'] = $this->translator->trans(
            'title.help',
            [],
            'HelpTextAdmin'
        );
        $navArray['overview']['group_items']['0']['adminCode'] = 'overview';
        $navArray['overview']['group_items']['0']['action'] = '';
        $navArray['overview']['group_items']['0']['title'] = $this->translator->trans(
            'overview.title',
            [],
            'HelpTextAdmin'
        );

        $navArray['dashboard']['group_name'] = 'Dashboard';
        $navArray['dashboard']['group_items']['0']['adminCode'] = 'dashboard';
        $navArray['dashboard']['group_items']['0']['action'] = '';
        $navArray['dashboard']['group_items']['0']['title'] = $this->translator->trans(
            'dashboard.title',
            [],
            'HelpTextAdmin'
        );

        foreach ($dashBoardGroups as $key => $group) {
                $navArray[$group['label']]['group_items'] = [];
                $i = 0;
                /** @var AdminInterface $admin */
            foreach ($group['items'] as $admin) {
                    if (0 == $i++) {
                        $navArray[$group['label']]['group_name'] = $this->translator->trans(
                            $admin->getLabel(),
                            [],
                            $admin->getTranslationDomain()
                        );
                    }

                    $help_text_result = $helpTextManager->searchHelpTextByKeyLocale($admin->getCode(), $locale);
                    if (count($help_text_result) > 0) {
                        foreach ($help_text_result as $row) {
                            //split Translation Key into adminCode and action
                            $strripos = strripos($row->getTranslationKey(), '.');
                            $action = substr($row->getTranslationKey(), $strripos + 1);
                            $navArray[$group['label']]['group_items'][$row->getId()]['adminCode'] = $admin->getCode(
                            );
                            $navArray[$group['label']]['group_items'][$row->getId()]['action'] = $action;
                            $navArray[$group['label']]['group_items'][$row->getId()]['title'] = $row->getTitle();
                        }
                    }
                }
        }

        return $navArray;
    }
}
