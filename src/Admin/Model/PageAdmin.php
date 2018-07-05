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

use Doctrine\ORM\Query;
use Networking\InitCmsBundle\Admin\BaseAdmin;
use Networking\InitCmsBundle\Filter\SimpleStringFilter;
use Networking\InitCmsBundle\Form\Type\AutocompleteType;
use Networking\InitCmsBundle\Form\Type\IconradioType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\CoreBundle\Form\Type\CollectionType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

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
    protected $baseRouteName = 'admin_networking_initcms_page';

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
    protected $pageManager;

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon-file';
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
        $collection->add('translate', 'translate/{id}/locale/{locale}', [], ['method' => 'POST']);
        $collection->add('copy', 'copy/{id}', [], ['method' => 'POST']);
        $collection->add('parentPageList', 'parent_page/', [], ['method' => 'GET']);
        $collection->add('pageSettings', 'page_settings/{id}', [], ['method' => 'GET']);
        $collection->add('link', 'link/{id}/locale/{locale}', [], ['method' => 'GET']);
        $collection->add('draft', 'draft/{id}', [], ['method' => 'GET']);
        $collection->add('review', 'review/{id}', [], ['method' => 'GET']);
        $collection->add('publish', 'publish/{id}', [], ['method' => 'GET']);
        $collection->add('offline', 'offline/{id}', [], ['method' => 'GET']);
        $collection->add('cancelDraft', 'cancel_draft/{id}', [], ['method' => 'GET']);
        $collection->add('getPath', 'get_path/', [], ['method' => 'GET']);
        $collection->add('batchTranslate', 'batch_translate', []);
        $collection->add(
            'unlink',
            'unlink/{id}/translation_id/{translationId}',
            [],
            ['method' => 'GET|DELETE']
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
            $request = $this->getContainer()->get('request_stack')->getCurrentRequest();
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

        $validationGroups = ['default'];

        $homePage = $pageManager->findOneBy(['isHome' => true, 'locale' => $this->pageLocale]);


        $this->canCreateHomepage = (!$homePage) ? true : false;

        if (!$this->canCreateHomepage) {
            if (!$this->getSubject()->getId() || !$this->getSubject()->isHome()) {
                $validationGroups[] = 'not_home';
            }
        }

        $this->formOptions['constraints'] = new \Symfony\Component\Validator\Constraints\Valid();


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

        $this->getRequest()->attributes->add(['page_locale' => $this->pageLocale]);

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
                    CollectionType::class,
                    [
                        'required' => true,
                        'label' => false,
                        'label_render' => false,
                        'error_bubbling' => false,
                        'type_options' => ['error_bubbling' => false]
                    ]
                )
                ->end();
        }


        $formMapper->with('page_settings');

        if ($this->canCreateHomepage && !$this->getSubject()->getId()) {
            $formMapper->add(
                'isHome',
                CheckboxType::class,
                ['required' => false],
                ['inline_block' => true]
            );
        }

        if (!$this->getSubject()->getId()) {

            $formMapper
                ->add(
                    'locale',
                    ChoiceType::class,
                    [
                        'choices' => $this->getLocaleChoices(),
                        'preferred_choices' => [$this->pageLocale],
                        'help_block' => 'locale.helper.text'
                    ]
                );

        }

        $formMapper->add(
            'pageName',
            TextType::class,
            ['help_block' => 'page_name.helper.text']
        );

        if (!$this->canCreateHomepage) {

            if (!$this->getSubject()->getId() || !$this->getSubject()->isHome()) {
                $formMapper
                    ->add(
                        'parent',
                        AutocompleteType::class,
                        [
                            'help_block' => 'parent.helper.text',
                            'attr' => ['style' => "width:220px"],
                            'property' => 'AdminTitle',
                            'class' => $this->getClass(),
                            'required' => false,
                            'query_builder' => $pageManager->getParentPagesQuery(
                                    $this->pageLocale,
                                    $this->getSubject()->getId(),
                                    false,
                                    false
                                ),
                        ]
                    );
            }

            if (!$this->getSubject()->getId() || !$this->getSubject()->isHome()) {
                $formMapper
                    ->add(
                        'alias',
                        AutocompleteType::class,
                        [
                            'help_block' => 'alias.helper.text',
                            'attr' => ['style' => "width:220px"],
                            'property' => 'AdminTitle',
                            'class' => $this->getClass(),
                            'required' => false,
                            'query_builder' => $pageManager->getParentPagesQuery(
                                    $this->pageLocale,
                                    $this->getSubject()->getId(),
                                    true,
                                    true
                                ),
                        ],
                        ['display_method' => 'getAliasFullPath']
                    );
            }

        }

        $requireUrl = $this->canCreateHomepage ? false : true;

        if ($this->getSubject()->getId()) {

            $attr = $this->getSubject()->isHome()?['readonly' => 'readonly']:[];
            $formMapper
                ->add(
                    'url',
                    TextType::class,
                    [
                        'required' => $requireUrl,
                        'help_label' => $this->getSubject()->getFullPath(),
                        'help_block' => 'url.helper.text'
                    ],
                    ['display_method' => 'getFullPath', 'attr' => $attr]
                );
        } elseif (!$this->getSubject()->getId()) {
            $formMapper
                ->add(
                    'url',
                    TextType::class,
                    [
                        'required' => $requireUrl,
                        'help_label' => '/',
                        'help_block' => 'url.helper.text'
                    ],
                    ['display_method' => 'getFullPath']
                );
        }

        $subject = $this->subject;
        $formMapper
            ->add(
                'visibility',
                ChoiceType::class,
                [
                    'help_block' => 'visibility.helper.text',
                    'choices' => $subject::getVisibilityList(),
                    'translation_domain' => $this->translationDomain
                ]
            )
            ->add('activeFrom',
                DateTimeType::class,
                [
                    'widget_form_group_attr' => ['class' => 'form-group form-inline'],
                    'required' => false,
                    'widget' => 'single_text',
                    'datetimepicker' => true,
                    'widget_reset_icon' => 'remove'
                ]
            )
            ->add('activeTo',
                DateTimeType::class,
                [
                    'widget_form_group_attr' => ['class' => 'form-group form-inline'],
                    'required' => false,
                    'widget' => 'single_text',
                    'datetimepicker' => true,
                    'widget_reset_icon' => 'remove'
                ])
            ->add(
                'templateName',
                IconradioType::class,
                [
                    'label' => 'form.label_template',
                    'horizontal_input_wrapper_class' => 'col-md-12',
                    'expanded' => true,
                    'choices' => $this->getPageTemplates(),
                    'data' => $this->getDefaultTemplate()
                ]
            );
        $formMapper->end();
        // end of group: page_settings
        $formMapper
            ->with('meta_settings')
            ->add('metaTitle', null, ['help_block' => 'meta_title.helper.text'])
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
                CallbackFilter::class,
                [
                    'callback' => [
                        $this,
                        'getByLocale'
                    ]
                ],
                ChoiceType::class,
                [
                    'placeholder' => false,
                    'choices' => $this->getLocaleChoices(),
                    'preferred_choices' => [$this->getDefaultLocale()]
                ]
            )
            ->add('pageName', SimpleStringFilter::class)
            ->add(
                'path',
                CallbackFilter::class,
                ['callback' => [$this, 'matchPath'], 'hidden' => true]
            )
            ->add(
                'status',
                SimpleStringFilter::class,
                ['hidden' => true],
                ChoiceType::class,
                [
                    'placeholder' => '-',
                    'choices' => [
                        PageInterface::STATUS_DRAFT => PageInterface::STATUS_DRAFT,
                        PageInterface::STATUS_REVIEW => PageInterface::STATUS_REVIEW,
                        PageInterface::STATUS_PUBLISHED => PageInterface::STATUS_PUBLISHED,
                    ],
                    'translation_domain' => $this->translationDomain
                ]
            );

    }

    /**
     * @param array $filterValues
     */
    public function configureDefaultFilterValues(array &$filterValues)
    {
        $filterValues['locale'] = [
            'type'  => \Sonata\AdminBundle\Form\Type\Filter\ChoiceType::TYPE_EQUAL,
            'value' => $this->getDefaultLocale(),
        ];
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
            return false;
        }
        $data['value'] = trim($data['value']);

        if (strlen($data['value']) == 0) {
            return false;
        }

        $fieldName = 'path';
        $qb = $ProxyQuery->getQueryBuilder();
        $qb->leftJoin(sprintf('%s.contentRoute', $alias), 'cpath');
        $parameterName = sprintf('%s_%s', $fieldName, $ProxyQuery->getUniqueParameterId());

        $qb->andWhere(sprintf('%s.%s LIKE :%s', 'cpath', $fieldName, $parameterName));
        $qb->setParameter(sprintf(':%s', $parameterName), '%' . $data['value'] . '%');

        return true;
    }

    /**
     * @param string $context
     * @return \Sonata\AdminBundle\Datagrid\ProxyQueryInterface
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        if($context == 'list'){
            $query->addSelect('c');
            $query->leftJoin(sprintf('%s.contentRoute', $query->getRootAlias()), 'c');
            $query->orderBy('c.path', 'asc');
        }


        return $query;
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
        $currentFilter = $this->getFilterParameters();
        $locale = $this->getDefaultLocale();
        if(array_key_exists('locale', $currentFilter)){
            $locale = $currentFilter['locale']['value'];
        }


        $rootMenus = $this->getModelManager()->findBy(
            'NetworkingInitCmsBundle:MenuItem',
            ['isRoot' => 1, 'locale' => $locale]
        );
        $listMapper
            ->add(
                'pageName',
                'string',
                ['template' => 'NetworkingInitCmsBundle:PageAdmin:page_title_list_field.html.twig']
            )
            ->add(
                'status',
                null,
                [
                    'label' => ' ',
                    'sortable' => false,
                    'template' => 'NetworkingInitCmsBundle:PageAdmin:page_status_list_field.html.twig'
                ]
            )
            ->add(
                'fullPath',
                null,
                [
                    'sortable' => false,
                ]
            )
            ->add(
                'getMenuItem',
                null,
                [
                    'sortable' => false,
                    'template' => 'NetworkingInitCmsBundle:PageAdmin:page_menu_list_field.html.twig',
                    'rootMenus' => $rootMenus,
                    'locale' => $locale
                ]
            )
        ;

        $listMapper->add(
            '_action',
            'actions',
            [
                'label' => ' ',
                'actions' => [
                    'edit' => [],
                    'show' => ['template' => 'NetworkingInitCmsBundle:PageAdmin:page_action_show.html.twig'],
                    'copy' => ['template' => 'NetworkingInitCmsBundle:PageAdmin:page_action_copy.html.twig'],
                    'delete' => [],
                ]
            ]
        );
    }

    /**
     * @param MenuItemInterface $menu
     * @param $action
     * @param AdminInterface $childAdmin
     * @return mixed|void
     */
    protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {

        if (!in_array($action, ['edit'])) {
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
                    $this->trans('View %language% version', ['%language%' => $language['label']]),
                    ['uri' => $admin->generateUrl('edit', ['id' => $page->getId()])]
                );

            } else {
                $menu->addChild(
                    $this->trans('Translate %language%', ['%language%' => $language['label']]),
                    [
                        'uri' => $admin->generateUrl(
                            'translatePage',
                            ['id' => $id, 'locale' => $language['locale']]
                        )
                    ]
                );
            }

        }
    }

    /**
     * {@inheritdoc}
     * @deprecated will be removed in alignment with sonata-project/admin-bundle
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {

        if (!in_array($action, ['edit'])) {
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
                    $this->trans('View %language% version', ['%language%' => $language['label']]),
                    ['uri' => $admin->generateUrl('edit', ['id' => $page->getId()])]
                );

            } else {
                $menu->addChild(
                    $this->trans('Translate %language%', ['%language%' => $language['label']]),
                    [
                        'uri' => $admin->generateUrl(
                                'translatePage',
                                ['id' => $id, 'locale' => $language['locale']]
                            )
                    ]
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
            return false;
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
                    [
                        'short_label' => $language['short_label'],
                        'translation' => $translatedLocales->get($language['locale']),
                        'label' => \Locale::getDisplayLanguage($language['locale'])
                    ]
                );

            } else {
                $translationLanguages->set(
                    $language['locale'],
                    [
                        'short_label' => $language['short_label'],
                        'translation' => false,
                        'label' => \Locale::getDisplayLanguage($language['locale'])
                    ]
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
        $choices = [];

        $templates = $this->getContainer()->getParameter('networking_init_cms.page.templates');
        foreach ($templates as $key => $template) {
            $choices[$template['name']] = $key;
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
        reset($templates);
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
        $icons = [];

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
        $actions = [];

        if ($this->isGranted('PUBLISH')) {
            $actions['publish'] = [
                'label' => $this->trans('label.action_publish', [], $this->translationDomain),
                'ask_confirmation' => true // If true, a confirmation will be asked before performing the action
            ];
        }

        if($this->isGranted('EDIT')){
            $actions['copy'] = [
                'label' => $this->trans('label.action_copy', [], $this->translationDomain),
            ];
        }

        if ($this->isGranted('PUBLISH')) {
            $actions['cache_clear'] = [
                'label' => $this->trans('label.action_cache_clear', [], $this->translationDomain),
            ];
        }

        if ($this->hasRoute('delete') && $this->isGranted('DELETE')) {
            $actions['delete'] = [
                'label' => $this->trans('action_delete', [], 'SonataAdminBundle'),
                'ask_confirmation' => true, // by default always true
            ];
        }

        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportFormats()
    {
        return [];
    }

    /**
     * @param PageInterface $object
     * @return mixed|void
     */
    public function postRemove($object)
    {
        $contentRoute = $object->getContentRoute();

        try{
            $this->getModelManager()->delete($contentRoute);
        }catch (\Exception $e){
            var_dump($e);
            die;
        }

    }

}
