<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Admin\Model;

use Networking\InitCmsBundle\Admin\BaseAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;

/**
 * Class PageAdmin
 * @package Networking\InitCmsBundle\Admin\Model
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class PageAdmin extends BaseAdmin
{
    /**
     * @var int
     */
    protected $maxPerPage = 500;

    /**
     * @var string
     */
    protected $baseRoutePattern = 'cms/pages';

    /**
     * @var string
     */
    protected $pageLocale;

    /**
     * @var bool
     */
    protected $canCreateHomepage = false;

    /**
     * @var string
     */
    protected $repository = '';

    /**
     * @var \Networking\InitCmsBundle\Model\PageManagerInterface
     */
    protected $pageManger;

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'icon-file-alt';
    }

    /**
     * @param \Networking\InitCmsBundle\Model\PageManagerInterface $pageManager
     */
    public function setPageManager(PageManagerInterface $pageManager)
    {
        $this->pageManager = $pageManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('translatePage', 'translate_page/{id}/locale/{locale}', array(), array('method' => 'POST'));
        $collection->add('parentPageList', 'parent_page/', array(), array('method' => 'GET'));
        $collection->add('updateFormFieldElement', 'update_form_fields', array(), array('method' => 'POST'));
        $collection->add('addLayoutBlock', 'add_layout_block', array(), array('method' => 'POST'));
        $collection->add('updateLayoutBlockSort', 'update_layout_block_sort', array(), array('method' => 'GET'));
        $collection->add('deleteLayoutBlock', 'delete_layout_block', array(), array('method' => 'GET'));
        $collection->add('link', 'link/{id}/locale/{locale}', array(), array('method' => 'GET'));
        $collection->add('draft', 'draft/{id}', array(), array('method' => 'GET'));
        $collection->add('review', 'review/{id}', array(), array('method' => 'GET'));
        $collection->add('publish', 'publish/{id}', array(), array('method' => 'GET'));
        $collection->add('cancelDraft', 'cancel_draft/{id}', array(), array('method' => 'GET'));
        $collection->add('getPath', 'get_path/', array(), array('method' => 'GET'));
        $collection->add(
            'unlink',
            'unlink/{id}/translation_id/{translationId}',
            array(),
            array('method' => 'GET|DELETE')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormBuilder()
    {
        try {
            $request = $this->getRequest();
        } catch (\RuntimeException $e) {
            $request = $this->getContainer()->get('request');
        }

        $this->pageLocale = $request->get('locale') ? $request->get('locale') : $this->getSubject()->getLocale();

        if (!$this->pageLocale) {
            throw new \Symfony\Component\Form\Exception\InvalidArgumentException('Cannot create a page without a language');
        }

        /** @var $pageManager PageManagerInterface */
        $pageManager = $this->getContainer()->get('networking_init_cms.page_manager');

        if ($this->getSubject()->getId()) {
            $this->pageLocale = $this->getSubject()->getLocale();
        }

        $validationGroups = array('default');

        $homePage = $pageManager->findOneBy(array('isHome' => true, 'locale' => $this->pageLocale));


        $this->canCreateHomepage = (!$homePage) ? true : false;

        if (!$this->canCreateHomepage) {
            if (!$this->getSubject()->getId() || !$this->getSubject()->isHome()) {
                $validationGroups[] = 'not_home';
            }
        }


        $this->formOptions['validation_groups'] = $validationGroups;

        return parent::getFormBuilder();

    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var $pageManager PageManagerInterface */
        $pageManager = $this->getContainer()->get('networking_init_cms.page_manager');
        try {
            $request = $this->getRequest();
        } catch (\RuntimeException $e) {
            $request = $this->getContainer()->get('request');
        }

        if ($this->getSubject()->getId() || $request->isXmlHttpRequest()) {
            $formMapper
                ->with('page_content')
                ->add(
                    'layoutBlock',
                    'sonata_type_collection',
                    array(
                        'required' => true,
                        'by_reference' => false
                    ),
                    array(
                        'edit' => 'inline',
                        'inline' => 'table',
                        'sortable' => 'sortOrder',
                        'no_label' => true
                    )
                )
                ->end();
        }


        $formMapper->with('page_settings');

        if ($this->canCreateHomepage && !$this->getSubject()->getId()) {
            $formMapper->add(
                'isHome',
                null,
                array('required' => false),
                array('inline_block' => true)
            );
        }

        if (!$this->getSubject()->getId()) {

            $formMapper
                ->add(
                    'locale',
                    'choice',
                    array(
                        'choices' => $this->getLocaleChoices(),
                        'preferred_choices' => array($this->pageLocale),
                        'help_inline' => 'locale.helper.text'
                    )
                );

        }

        $formMapper->add(
            'pageName',
            null,
            array('help_inline' => 'page_name.helper.text')
        );

        if (!$this->canCreateHomepage) {

            if (!$this->getSubject()->getId() || !$this->getSubject()->isHome()) {
                $formMapper
                    ->add(
                        'parent',
                        'networking_type_autocomplete',
                        array(
                            'help_inline' => 'parent.helper.text',
                            'attr' => array('style' => "width:220px"),
                            'property' => 'AdminTitle',
                            'class' => 'Application\\Networking\\InitCmsBundle\\Entity\\Page',
                            'required' => false,
                            'query_builder' => $pageManager->getParentPagesQuery(
                                    $this->pageLocale,
                                    $this->getSubject()->getId()
                                ),
                        )
                    );
            }
        }

        $requireUrl = $this->canCreateHomepage ? false : true;

        if ($this->getSubject()->getId()) {
            $formMapper
                ->add(
                    'url',
                    null,
                    array(
                        'required' => $requireUrl,
                        'read_only' => $this->getSubject()->isHome(),
                        'help_label' => $this->getSubject()->getFullPath(),
                        'help_inline' => 'url.helper.text'
                    ),
                    array('display_method' => 'getFullPath')
                );
        } elseif (!$this->getSubject()->getId()) {
            $formMapper
                ->add(
                    'url',
                    null,
                    array(
                        'required' => $requireUrl,
                        'help_label' => '/',
                        'help_inline' => 'url.helper.text'
                    ),
                    array('display_method' => 'getFullPath')
                );
        }

        $subject = $this->subject;
        $formMapper
            ->add(
                'visibility',
                'sonata_type_translatable_choice',
                array(
                    'help_inline' => 'visibility.helper.text',
                    'choices' => $subject::getVisibilityList(),
                    'catalogue' => $this->translationDomain
                )
            )
            ->add(
                'templateName',
                'networking_type_iconradio',
                array(
                    'label' => 'form.label_template',
                    'expanded' => true,
                    'choices' => $this->getPageTemplates(),
                    'data' => $this->getDefaultTemplate()
                )
            );
        $formMapper->end();
        // end of group: page_settings
        $formMapper
            ->with('meta_settings', array('collapsed' => true))
            ->add('metaTitle', null, array('help_inline' => 'meta_title.helper.text'))
            ->add('metaKeyword')
            ->add('metaDescription')
            ->end();

    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate($name)
    {

        switch ($name) {
            case 'show':
                return 'NetworkingInitCmsBundle:PageAdmin:page_show.html.twig';
                break;
            case 'preview':
                return 'NetworkingInitCmsBundle:PageAdmin:page_preview.html.twig';
                break;
            case 'edit':
                if ($this->getSubject()->getId()) {
                    return 'NetworkingInitCmsBundle:PageAdmin:page_edit.html.twig';
                } else {
                    return 'NetworkingInitCmsBundle:PageAdmin:page_create.html.twig';
                }
                break;
            case 'preview':
                return 'NetworkingInitCmsBundle:PageAdmin:page_preview.html.twig';
                break;
            case 'list':
                return 'NetworkingInitCmsBundle:PageAdmin:page_list.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'locale',
                'doctrine_orm_callback',
                array(
                    'callback' => array(
                        $this,
                        'getByLocale'
                    )
                ),
                'choice',
                array(
                    'empty_value' => false,
                    'choices' => $this->getLocaleChoices(),
                    'preferred_choices' => array($this->getDefaultLocale())
                )
            )
            ->add('pageName', 'networking_init_cms_simple_string')
            ->add(
                'path',
                'doctrine_orm_callback',
                array('callback' => array($this, 'matchPath'), 'hidden' => true)
            )
            ->add(
                'status',
                'doctrine_orm_choice',
                array('hidden' => true),
                'sonata_type_translatable_choice',
                array(
                    'empty_value' => '-',
                    'choices' => array(
                        PageInterface::STATUS_DRAFT => PageInterface::STATUS_DRAFT,
                        PageInterface::STATUS_REVIEW => PageInterface::STATUS_REVIEW,
                        PageInterface::STATUS_PUBLISHED => PageInterface::STATUS_PUBLISHED,
                    ),
                    'catalogue' => $this->translationDomain
                )
            );

    }

    /**
     * @param ProxyQuery $ProxyQuery
     * @param $alias
     * @param $field
     * @param $data
     * @return bool
     */
    public function matchPath(ProxyQuery $ProxyQuery, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
            return;
        }
        $data['value'] = trim($data['value']);

        if (strlen($data['value']) == 0) {
            return;
        }

        $fieldName = 'path';
        $qb = $ProxyQuery->getQueryBuilder();
        $qb->leftJoin(sprintf('%s.contentRoute', $alias), 'c');
        $parameterName = sprintf('%s_%s', $fieldName, $ProxyQuery->getUniqueParameterId());

        $qb->andWhere(sprintf('%s.%s LIKE :%s', 'c', $fieldName, $parameterName));
        $qb->setParameter(sprintf(':%s', $parameterName), '%' . $data['value'] . '%');

        return true;
    }

    /**
     * @param ProxyQuery $ProxyQuery
     * @param $alias
     * @param $field
     * @param $data
     * @return bool
     */
    public function getByLocale(ProxyQuery $ProxyQuery, $alias, $field, $data)
    {
        $active = true;
        if (!$locale = $data['value']) {
            $locale = $this->getDefaultLocale();
            $active = false;
        }
        $qb = $ProxyQuery->getQueryBuilder();
        $qb->andWhere(sprintf('%s.locale = :locale', $alias));
        $qb->orderBy(sprintf('%s.path', $alias), 'asc');
        $qb->setParameter(':locale', $locale);

        return $active;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {

        $listMapper
            ->addIdentifier(
                'adminTitle',
                'string',
                array('template' => 'NetworkingInitCmsBundle:PageAdmin:page_title_list_field.html.twig')
            )
            ->add(
                'status',
                null,
                array(
                    'label' => ' ',
                    'sortable' => false,
                    'template' => 'NetworkingInitCmsBundle:PageAdmin:page_status_list_field.html.twig'
                )
            )
            ->add(
                'fullPath',
                null,
                array(
                    'sortable' => false,
                    'template' => 'NetworkingInitCmsBundle:PageAdmin:page_title_list_field.html.twig'
                )
            );

        $listMapper->add(
            '_action',
            'actions',
            array(
                'label' => ' ',
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {

        if (!in_array($action, array('edit'))) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;

        if (!$id = $this->getRequest()->get('id')) {
            return;
        }

        /** @var $pageManager PageManagerInterface */
        $pageManager = $this->getContainer()->get('networking_init_cms.page_manager');

        /** @var $page PageInterface */
        $page = $pageManager->findById($id);

        $translatedLocales = $page->getAllTranslations();

        $originalLocale = $page->getLocale();
        foreach ($this->languages as $language) {

            if ($language['locale'] == $originalLocale) {
                continue;
            }

            if ($translatedLocales->containsKey($language['locale'])) {

                $page = $translatedLocales->get($language['locale']);
                $menu->addChild(
                    $this->trans('View %language% version', array('%language%' => $language['label'])),
                    array('uri' => $admin->generateUrl('edit', array('id' => $page->getId())))
                );

            } else {
                $menu->addChild(
                    $this->trans('Translate %language%', array('%language%' => $language['label'])),
                    array(
                        'uri' => $admin->generateUrl(
                                'translatePage',
                                array('id' => $id, 'locale' => $language['locale'])
                            )
                    )
                );
            }

        }
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTranslationLanguages()
    {
        $translationLanguages = new \Doctrine\Common\Collections\ArrayCollection();

        if (!$id = $this->getRequest()->get('id')) {
            return;
        }

        /** @var $pageManager PageManagerInterface */
        $pageManager = $this->getContainer()->get('networking_init_cms.page_manager');

        /** @var $page PageInterface */
        $page = $pageManager->findById($id);

        $translatedLocales = $page->getAllTranslations();

        $originalLocale = $page->getLocale();

        foreach ($this->languages as $language) {

            if ($language['locale'] == $originalLocale) {
                continue;
            }

            if ($translatedLocales->containsKey($language['locale'])) {
                $translationLanguages->set(
                    $language['locale'],
                    array(
                        'short_label' => $language['short_label'],
                        'translation' => $translatedLocales->get($language['locale']),
                        'label' => \Locale::getDisplayLanguage($language['locale'])
                    )
                );

            } else {
                $translationLanguages->set(
                    $language['locale'],
                    array(
                        'short_label' => $language['short_label'],
                        'translation' => false,
                        'label' => \Locale::getDisplayLanguage($language['locale'])
                    )
                );
            }
        }

        return $translationLanguages;
    }

    /**
     * Get the page templates from the configuration and create an array to use in the
     * choice field in the admin form
     *
     * @return array
     */
    protected function getPageTemplates()
    {
        $choices = array();

        $templates = $this->getContainer()->getParameter('networking_init_cms.page.templates');
        foreach ($templates as $key => $template) {
            $choices[$key] = $template['name'];
        }

        return $choices;
    }

    /**
     * If there is an object get the object template variable, else get first in template array
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        if ($this->getSubject()->getId()) {
            return $this->getSubject()->getTemplateName();
        }
        $templates = $this->getContainer()->getParameter('networking_init_cms.page.templates');
        $firstTemplate = reset($templates);
        $defaultTemplate = key($templates);

        return $defaultTemplate;
    }

    /**
     * Get the icons which represent the templates
     *
     * @return array
     */
    protected function getPageTemplateIcons()
    {
        $icons = array();

        $templates = $this->getContainer()->getParameter('networking_init_cms.page.templates');
        foreach ($templates as $key => $template) {
            $icons[$key] = isset($template['icon']) ? $template['icon'] : '';
        }

        return $icons;
    }


    /**
     * {@inheritdoc}
     */
    public function getBatchActions()
    {
        // retrieve the default (currently only the delete action) actions
        $actions = array();

        // check user permissions
        if ($this->isGranted('PUBLISH')) {
            $actions['publish'] = array(
                'label' => $this->trans('label.action_publish', array(), $this->translationDomain),
                'ask_confirmation' => true // If true, a confirmation will be asked before performing the action
            );

        }

        if ($this->hasRoute('delete') && $this->isGranted('DELETE')) {
            $actions['delete'] = array(
                'label' => $this->trans('action_delete', array(), 'SonataAdminBundle'),
                'ask_confirmation' => true, // by default always true
            );
        }

        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportFormats()
    {
        return array();
    }

}
