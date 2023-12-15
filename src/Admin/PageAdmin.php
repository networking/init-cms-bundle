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

namespace Networking\InitCmsBundle\Admin;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Loggable\Entity\LogEntry;
use Networking\InitCmsBundle\Entity\BasePage;
use Networking\InitCmsBundle\Entity\LayoutBlock;
use Networking\InitCmsBundle\Entity\MenuItem;
use Networking\InitCmsBundle\Filter\SimpleStringFilter;
use Networking\InitCmsBundle\Form\Type\AutocompleteType;
use Networking\InitCmsBundle\Form\Type\IconradioType;
use Networking\InitCmsBundle\Form\Type\MediaEntityType;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\CollectionType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * Class PageAdmin.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageAdmin extends BaseAdmin
{
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


    private ?LogEntry $lastEditedBy = null;

    public function __construct(
        protected PageManagerInterface $pageManager,
        protected array $pageTemplates,
    )
    {
    }

    protected function generateBaseRoutePattern(bool $isChildAdmin = false
    ): string {
        return 'cms/pages';
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'admin_networking_initcms_page';
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_BY] = 'path';
        $sortValues[DatagridInterface::SORT_ORDER] = 'ASC';
        parent::configureDefaultSortValues($sortValues);
    }

    protected function configureRoutes(RouteCollectionInterface $collection
    ): void {
        $collection->add(
            'translate',
            'translate/{id}/locale/{locale}',
            [],
            ['method' => 'POST'],
            ['expose' => true]
        );
        $collection->add('copy', 'copy/{id}', [], ['method' => 'POST']);
        $collection->add(
            'parentPageList',
            'parent_page/',
            [],
            ['method' => 'GET']
        );
        $collection->add(
            'pageSettings',
            'page_settings/{id}',
            [],
            ['method' => 'GET']
        );
        $collection->add(
            'link',
            'link/{id}/locale/{locale}',
            [],
            ['method' => 'GET']
        );
        $collection->add('draft', 'draft/{id}', [], ['method' => 'GET']);
        $collection->add('review', 'review/{id}', [], ['method' => 'GET']);
        $collection->add('publish', 'publish/{id}', [], ['method' => 'GET']);
        $collection->add('offline', 'offline/{id}', [], ['method' => 'GET']);
        $collection->add(
            'cancelDraft',
            'cancel_draft/{id}',
            [],
            ['method' => 'GET']
        );
        $collection->add('getPath', 'get_path/', [], ['method' => 'GET']);
        $collection->add('batchTranslate', 'batch_translate', []);
        $collection->add(
            'editPageSettings',
            'edit_page_settings/{id}/{no_layout}',
            ['no_layout' => false]
        );
        $collection->add(
            'unlink',
            'unlink/{id}/translation_id/{translationId}',
            [],
            ['method' => 'GET|DELETE']
        );
        $collection->add(
            'getPageStatus',
            'page_status/{id}/',
            [],
            ['method' => 'GET']
        );

        foreach ($collection->getElements() as $key => $element) {
            $collection->get($key)->setOption('expose', true);
        }
    }

    public function configureFormOptions(array &$formOptions): void
    {
        $request = $this->getRequest();

        if ($this->hasObject()) {
            $this->pageLocale = $this->getSubject()->getLocale();
            $formOptions['attr'] = ['class' => 'row'];
        } else {
            $this->pageLocale = $request->get('locale')
                ?: $request->getDefaultLocale();

            if ('POST' === $request->getMethod()) {
                $data = $request->request->all($this->getUniqId());
                $this->pageLocale = $data['locale'];
            }
        }

        if (!$this->pageLocale) {
            throw new InvalidArgumentException('Cannot create a page without a language');
        }
        $validationGroups = ['default'];

        $homePage = $this->pageManager->findOneBy(
            ['isHome' => true, 'locale' => $this->pageLocale]
        );

        $this->canCreateHomepage = !$homePage;

        if (!$this->canCreateHomepage) {
            if (!$this->hasObject() || !$this->getSubject()->isHome()) {
                $validationGroups[] = 'not_home';
            }
        }

        $formOptions['constraints'] = new Valid();

        $formOptions['validation_groups'] = $validationGroups;

        if ($this->getSubject()?->getId()) {
            $formOptions['method'] = 'PATCH';
        }
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $this->getRequest()->attributes->add(
            ['page_locale' => $this->pageLocale]
        );

        $request = $this->getRequest();
        if (($this->hasObject() || $request->isXmlHttpRequest())
            && !$request->get('no_layout')
        ) {
            $form
                ->with('page_content')
                ->add(
                    'layoutBlocks',
                    CollectionType::class,
                    [
                        'required' => true,
                        'label' => false,
                        'label_render' => false,
                        'error_bubbling' => false,
                        'type_options' => ['error_bubbling' => false],
                    ]
                )
                ->end();
        }

        $form->with('page_settings', ['label' => false]);

        if ($this->canCreateHomepage && !$this->hasObject()) {
            $form->add(
                'isHome',
                CheckboxType::class,
                ['required' => false],
                ['inline_block' => true]
            );
        }

        if (!$this->hasObject()) {
            $form
                ->add(
                    'locale',
                    ChoiceType::class,
                    [
                        'choices' => $this->getLocaleChoices(),
                        'choice_translation_domain' => false,
                        'preferred_choices' => [$this->pageLocale],
                        'help' => 'locale.helper.text',
                        'row_attr' => ['class' => 'form-floating mb-3'],
                    ]
                );
        }

        $form->add(
            'pageName',
            TextType::class,
            [
                'row_attr' => ['class' => 'form-floating mb-3'],
                'help' => 'page_name.helper.text',
            ]
        );

        if (!$this->canCreateHomepage) {
            if (!$this->hasObject() || !$this->getSubject()->isHome()) {
                $form
                    ->add(
                        'parent',
                        AutocompleteType::class,
                        [
                            'help' => 'parent.helper.text',
                            'choice_label' => 'AdminTitle',
                            'class' => $this->getClass(),
                            'row_attr' => ['class' => 'form-floating mb-4'],
                            'required' => false,
                            'query_builder' => $this->pageManager->getParentPagesQuery(
                                $this->pageLocale,
                                $this->hasSubject() ? $this->getSubject()
                                    ->getId() : null,
                                false,
                                false
                            ),
                        ]
                    );
            }

            if (!$this->hasObject() || !$this->getSubject()->isHome()) {
                $form
                    ->add(
                        'alias',
                        AutocompleteType::class,
                        [
                            'help' => 'alias.helper.text',
                            'choice_label' => 'AdminTitle',
                            'row_attr' => ['class' => 'form-floating mb-4'],
                            'class' => $this->getClass(),
                            'required' => false,
                            'query_builder' => $this->pageManager->getParentPagesQuery(
                                $this->pageLocale,
                                $this->hasObject() ? $this->getSubject()->getId(
                                ) : null,
                                true,
                                true
                            ),
                        ],
                        ['display_method' => 'getAliasFullPath']
                    );
            }
        }

        $requireUrl = $this->canCreateHomepage ? false : true;

        if ($this->hasObject()) {
            if (!$this->getSubject()->isHome()) {
                $form
                    ->add(
                        'url',
                        TextType::class,
                        [
                            'required' => false,
                            'row_attr' => ['class' => 'form-floating mb-4'],
                        ],
                        ['display_method' => 'getFullPath']
                    );
            }
        } else {
            $form
                ->add(
                    'url',
                    TextType::class,
                    [
                        'required' => $requireUrl,
                        //                        'help_label' => '/',
                        'help' => 'url.helper.text',
                        'row_attr' => ['class' => 'form-floating mb-4'],
                    ],
                    ['display_method' => 'getFullPath']
                );
        }

        $form
            ->add(
                'visibility',
                ChoiceType::class,
                [
                    'help_block' => 'visibility.helper.text',
                    'choices' => BasePage::getVisibilityList(),
                    'translation_domain' => $this->getTranslationDomain(),

                    'row_attr' => ['class' => 'form-floating mb-4'],
                ]
            )
            ->add(
                'activeFrom',
                DateTimeType::class,
                [
                    'widget_form_group_attr' => ['class' => 'form-group'],
                    'format' => 'dd.MM.yyyy HH:mm',
                    'label_render' => true,
                    'required' => false,
                    'widget' => 'single_text',
                    'html5' => false,
                    'row_attr' => ['class' => 'form-floating mb-4 col-6'],
                ]
            )
            ->add(
                'activeTo',
                DateTimeType::class,
                [
                    'widget_form_group_attr' => ['class' => 'form-group'],
                    'format' => 'dd.MM.yyyy HH:mm',
                    'required' => false,
                    'widget' => 'single_text',
                    'html5' => false,
                    'attr' => ['data-start-view' => 'hour'],
                    'row_attr' => ['class' => 'form-floating mb-4 col-6'],
                ]
            )
            ->add(
                'templateName',
                IconradioType::class,
                [
                    'label' => 'form.label_template',
                    'horizontal_input_wrapper_class' => 'col-md-12',
                    'expanded' => true,
                    'choices' => $this->getPageTemplates(),
                    'data' => $this->getDefaultTemplate(),
                    'choice_translation_domain' => 'messages',
                ]
            );
        $form->end();
        // end of group: page_settings
        $form
            ->with('meta_settings', ['label' => 'meta_settings'])
            ->add(
                'metaTitle',
                null,
                [
                    'help_block' => 'meta_title.helper.text',
                    'row_attr' => ['class' => 'form-floating mb-4'],
                ]
            )
            ->add(
                'metaKeyword',
                null,
                [
                    'row_attr' => ['class' => 'form-floating mb-4'],
                ]
            )
            ->add(
                'metaDescription',
                null,
                [
                    'row_attr' => ['class' => 'form-floating mb-4'],
                    'attr' => ['class' => 'h-500px'],
                ]
            )
            ->add(
                'socialMediaImage',
                MediaEntityType::class,
                [
                    'provider_name' => 'sonata.media.provider.image',
                    'widget_form_group_attr' => ['class' => 'form-group form-inline'],
                    'required' => false,
                ]
            )
            ->end();

        foreach ($this->getExtensions() as $extension) {
            $extension->configureFormFields($form);
        }
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add(
                'locale',
                CallbackFilter::class,
                [
                    'advanced_filter' => false,
                    'callback' => $this->getByLocale(...),
                ],
                [
                    'field_type' => LanguageType::class,
                    'field_options' => [
                        'row_attr' => ['class' => 'form-floating'],
                        'choice_loader' => new CallbackChoiceLoader(
                            fn () => $this->getLocaleChoices()
                        ),
                        'preferred_choices' => [$this->getDefaultLocale()],
                        'translation_domain' => $this->getTranslationDomain(),
                    ],
                ]
            )
            ->add(
                'pageName',
                SimpleStringFilter::class,
                [],
                [
                    'translation_domain' => $this->getTranslationDomain(),
                    'field_options' => [
                        'row_attr' => ['class' => 'form-floating'],
                    ],
                ]
            )
            ->add(
                'path',
                CallbackFilter::class,
                [
                    'callback' => $this->matchPath(...),
                    'advanced_filter' => false,
                ],
                [
                    'field_options' => [
                        'row_attr' => ['class' => 'form-floating'],
                    ],
                ]
            )
            ->add(
                'activeFrom',
                DateRangeFilter::class,
                [
                    'field_type' => DateRangePickerType::class,
                    'advanced_filter' => false,
                ],
                [
                    'label' => 'activeFrom',
                    'field_options' => [
                        'attr' => ['class' => 'd-flex flex-row'],
                        'field_options_start' => [
                            'format' => 'dd.MM.yyyy',
                            'widget' => 'single_text',
                            'html5' => false,
                            'row_attr' => ['class' => 'col me-1 form-floating'],
                        ],
                        'field_options_end' => [
                            'format' => 'dd.MM.yyyy',
                            'widget' => 'single_text',
                            'html5' => false,
                            'row_attr' => ['class' => 'col form-floating'],
                        ],
                    ],
                ]
            )
            ->add(
                'status',
                ChoiceFilter::class,
                [],
                [
                    'field_type' => ChoiceType::class,
                    'field_options' => [
                        'row_attr' => ['class' => 'form-floating'],
                        'placeholder' => 'empty_option',
                        'choices' => [
                            PageInterface::STATUS_DRAFT => PageInterface::STATUS_DRAFT,
                            PageInterface::STATUS_REVIEW => PageInterface::STATUS_REVIEW,
                            PageInterface::STATUS_PUBLISHED => PageInterface::STATUS_PUBLISHED,
                        ],
                        'translation_domain' => $this->getTranslationDomain(),
                    ],
                ]
            );

        foreach ($this->getExtensions() as $extension) {
            $extension->configureDatagridFilters($filter);
        }
    }

    public function configureDefaultFilterValues(array &$filterValues): void
    {
        $filterValues['locale'] = [
            'type' => ContainsOperatorType::TYPE_EQUAL,
            'value' => $this->getDefaultLocale(),
        ];
    }

    /**
     * @return bool
     */
    public function matchPath(ProxyQuery $ProxyQuery, $alias, $field, $data)
    {
        if (!$data || !$data instanceof FilterData || !$data->hasValue()) {
            return false;
        }
        $value = trim((string) $data->getValue());

        if (0 == strlen($value)) {
            return false;
        }

        $qb = $ProxyQuery->getQueryBuilder();
        $qb->leftJoin(sprintf('%s.contentRoute', $alias), 'cpath');
        $parameterName = sprintf(
            '%s_%s',
            $field,
            $ProxyQuery->getUniqueParameterId()
        );

        $qb->andWhere(
            sprintf('%s.%s LIKE :%s', 'cpath', $field, $parameterName)
        );
        $qb->setParameter(
            sprintf(':%s', $parameterName),
            '%'.$value.'%'
        );

        return true;
    }

    /**
     * @param ProxyQuery $query
     */
    public function configureQuery(ProxyQueryInterface $query
    ): ProxyQueryInterface {
        $qb = $query->getQueryBuilder();
        $alias = $qb->getRootAliases();
        $qb->addSelect('c');
        $qb->leftJoin($alias[0].'.contentRoute', 'c');
        $qb->orderBy('c.path', 'asc');

        return $query;
    }

    /**
     * @param FilterData $data
     *
     * @return bool
     */
    public function getByLocale(ProxyQuery $ProxyQuery, $alias, $field, $data)
    {
        if (is_array($data)) {
            $data = FilterData::fromArray($data);
        }

        if (!$locale = $data->getValue()) {
            $locale = $this->getDefaultLocale();
        }

        $alias = $ProxyQuery->getRootAlias();
        $ProxyQuery->andWhere(sprintf('%s.%s = :locale', $alias, $field));
        $ProxyQuery->orderBy(sprintf('%s.path', $alias), 'asc');
        $ProxyQuery->setParameter(':locale', $locale);

        return true;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $currentFilter = $this->getFilterParameters();
        $locale = $this->getDefaultLocale();

        if (array_key_exists('locale', $currentFilter)) {
            $locale = $currentFilter['locale']['value'];
        }

        $rootMenus = $this->getModelManager()->findBy(
            MenuItem::class,
            ['isRoot' => 1, 'locale' => $locale]
        );
        $list
            ->add(
                'pageName',
                'string',
                [
                    'template' => '@NetworkingInitCms/PageAdmin/page_title_list_field.html.twig',
                    'sortable' => false,
                ]
            )
            ->add(
                'status',
                null,
                [
                    'label' => false,
                    'sortable' => false,
                    'template' => '@NetworkingInitCms/PageAdmin/page_status_list_field.html.twig',
                ]
            )
            ->add(
                'fullPath',
                null,
                [
                    'sortable' => false,
                    'template' => '@NetworkingInitCms/PageAdmin/page_list_show_field.html.twig',
                ]
            )
            ->add(
                'getMenuItem',
                null,
                [
                    'sortable' => false,
                    'template' => '@NetworkingInitCms/PageAdmin/page_menu_list_field.html.twig',
                    'rootMenus' => $rootMenus,
                    'locale' => $locale,
                ]
            );

        $list->add(
            '_action',
            'actions',
            [
                'label' => false,
                'actions' => [
                    'edit' => [],
                    'show' => ['template' => '@NetworkingInitCms/PageAdmin/page_action_show.html.twig'],
                    'copy' => ['template' => '@NetworkingInitCms/PageAdmin/page_action_copy.html.twig'],
                    'delete' => [],
                ],
            ]
        );

        foreach ($this->getExtensions() as $extension) {
            $extension->configureListFields($list);
        }
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTranslationLanguages()
    {
        $translationLanguages = new ArrayCollection();

        if (!$id = $this->getRequest()->get('id')) {
            return $translationLanguages;
        }

        /** @var $page PageInterface */
        $page = $this->pageManager->findById($id);

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
                        'translation' => $translatedLocales->get(
                            $language['locale']
                        ),
                        'label' => \Locale::getDisplayLanguage(
                            $language['locale']
                        ),
                    ]
                );
            } else {
                $translationLanguages->set(
                    $language['locale'],
                    [
                        'short_label' => $language['short_label'],
                        'translation' => false,
                        'label' => \Locale::getDisplayLanguage(
                            $language['locale']
                        ),
                    ]
                );
            }
        }

        return $translationLanguages;
    }

    /**
     * Get the page templates from the configuration and create an array to use in the
     * choice field in the admin form.
     *
     * @return array
     */
    protected function getPageTemplates()
    {
        $choices = [];

        foreach ($this->pageTemplates as $key => $template) {
            $choices[$template['name']] = $key;
        }

        return $choices;
    }

    /**
     * If there is an object get the object template variable, else get first in template array.
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        if ($this->hasObject()) {
            return $this->getSubject()->getTemplateName();
        }
        $defaultTemplate = array_key_first($this->pageTemplates);

        return $defaultTemplate;
    }

    /**
     * Get the icons which represent the templates.
     *
     * @return array
     */
    protected function getPageTemplateIcons()
    {
        $icons = [];

        foreach ($this->pageTemplates as $key => $template) {
            $icons[$key] = $template['icon'] ?? '';
        }

        return $icons;
    }

    public function configureBatchActions(array $actions): array
    {
        unset($actions['delete']);
        if ($this->isGranted('PUBLISH')) {
            $actions['publish'] = [
                'label' => 'label.action_publish',
                'ask_confirmation' => true,
                // If true, a confirmation will be asked before performing the action
            ];
        }

        if ($this->isGranted('EDIT')) {
            $actions['copy'] = [
                'label' => 'label.action_copy',
            ];
        }

        if ($this->isGranted('PUBLISH')) {
            $actions['cache_clear'] = [
                'label' => 'label.action_cache_clear',
            ];
        }

        if ($this->hasRoute('delete') && $this->isGranted('DELETE')) {
            $actions['delete'] = [
                'label' => 'label.action_delete',
                'ask_confirmation' => true, // by default always true
            ];
        }

        return $actions;
    }

    public function getExportFormats(): array
    {
        return [];
    }

    /**
     * @param PageInterface $object
     *
     * @return mixed|void
     */
    public function postRemove(object $object): void
    {
        $contentRoute = $object->getContentRoute();

        try {
            $this->getModelManager()->delete($contentRoute);
        } catch (\Exception) {
            exit;
        }
    }

    public function hasObject()
    {
        return $this->hasSubject() && $this->getSubject()->getId();
    }

    public function getPageByLayoutBlock(LayoutBlock $layoutBlock
    ): PageInterface {
        return $layoutBlock->getPage();
    }

    public function getLastEditedBy(): ?LogEntry
    {
        if ($this->lastEditedBy) {
            return $this->lastEditedBy;
        }

        $loggableClass = LogEntry::class;
        /** @var \Gedmo\Loggable\Entity\Repository\LogEntryRepository $repo */
        $repo = $this->getModelManager()->getEntityManager($loggableClass)
            ->getRepository($loggableClass);

        $this->lastEditedBy = $repo->findOneBy([
            'objectClass' => $this->getClass(),
            'objectId' => $this->getSubject()->getId(),
        ], ['version' => 'DESC']);

        return $this->lastEditedBy;
    }
}
