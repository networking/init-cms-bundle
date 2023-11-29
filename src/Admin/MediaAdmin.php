<?php

declare(strict_types=1);

/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Admin;

use Networking\InitCmsBundle\Filter\SimpleStringFilter;
use Networking\InitCmsBundle\Form\DataTransformer\TagTransformer;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\MediaBundle\Admin\BaseMediaAdmin as Admin;
use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Sonata\MediaBundle\Provider\FileProvider;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class MediaAdmin.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MediaAdmin extends Admin
{
    /**
     * @var array
     * @var array
     */
    protected $trackedActions = ['list'];

    /**
     * @var array
     */
    protected $languages;

    /**
     * @var int
     */
    protected $maxPerPage = 64;

    /**
     * @var array
     */
    protected $localisedMediaProviders = ['sonata.media.provider.file'];

    /**
     * @var bool
     */
    protected $hasMultipleMediaTags;

    /**
     * @var bool
     */
    protected $showTagTree;

    public function __construct(
        $pool,
        private readonly ValidatorInterface $validator
    ) {
        parent::__construct($pool);
    }

    public function validate(object $object)
    {
        return $this->validator->validate($object);
    }

    public function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'cms/media';
    }

    /**
     * Set the language paramenter to contain a list of languages most likely
     * passed from the config.yaml file.
     */
    public function setLanguages(array $languages)
    {
        $this->languages = $languages;
    }

    public function getTrackedActions(): array
    {
        return $this->trackedActions;
    }

    /**
     * @return $this
     */
    public function setTrackedActions($trackedActions)
    {
        $this->trackedActions = $trackedActions;

        return $this;
    }

    /**
     * @return $this
     */
    public function setMultipleMediaTags($hasMultipleMediaTags)
    {
        $this->hasMultipleMediaTags = $hasMultipleMediaTags;

        return $this;
    }

    /**
     * @param $showTagTree boolean
     *
     * @return $this
     */
    public function setShowTagTree($showTagTree)
    {
        $this->showTagTree = $showTagTree;

        return $this;
    }

    /**
     * @return bool
     */
    public function getShowTagTree()
    {
        return $this->showTagTree;
    }

    public function configureRoutes(RouteCollectionInterface $collection): void
    {
        parent::configureRoutes($collection);
        $collection->add(
            'init_ckeditor_browser',
            'init_ckeditor_browser',
            [
                '_controller' => 'Networking\InitCmsBundle\Controller\CkeditorAdminController::browser',
            ],
            [],
            ['expose' => true]
        );
        $collection->add(
            'init_ckeditor_browser_refresh',
            'init_ckeditor_browser_refresh',
            [
                '_controller' => 'Networking\InitCmsBundle\Controller\CkeditorAdminController::browserRefresh',
            ],
            [],
            ['expose' => true]
        );

        $collection->add(
            'init_ckeditor_upload_file',
            'init_ckeditor_upload_file',
            [
                '_controller' => 'Networking\InitCmsBundle\Controller\CkeditorAdminController::upload',
                'type' => 'file',
                'method' => 'POST',
            ],
            [],
            ['expose' => true]
        );

        $collection->add(
            'init_ckeditor_upload_image',
            'init_ckeditor_upload_image',
            [
                '_controller' => 'Networking\InitCmsBundle\Controller\CkeditorAdminController::upload',
                'type' => 'image',
                'method' => 'POST',
            ],
            [],
            ['expose' => true]
        );

        $collection->add(
            'init_clone',
            'clone',
            [
                '_controller' => 'Networking\InitCmsBundle\Controller\MediaAdminController::clone',
            ]
        );

        $collection->add(
            'refresh_list',
            'refresh_list',
            [
                '_controller' => 'Networking\InitCmsBundle\Controller\MediaAdminController::refreshList',
            ]
        );

        $collection->add(
            'pdf_preview',
            'pdf/view/{id}',
            [
                '_controller' => 'Networking\InitCmsBundle\Controller\MediaAdminController::previewPdf',
            ]
        );

        $collection->add(
            'gallery',
            'gallery',
            [
                '_controller' => 'Networking\InitCmsBundle\Controller\MediaAdminController::gallery',
            ]
        );

        $collection
            ->add(
                'gallery_select',
                'gallery_select',
                [
                    '_controller' => 'Networking\InitCmsBundle\Controller\MediaAdminController::gallerySelect',
                ],
                [],
                ['expose' => true]
            )
            ->add(
                'gallery_select_refresh',
                'gallery_select_refresh',
                [
                    '_controller' => 'Networking\InitCmsBundle\Controller\MediaAdminController::gallerySelectRefresh',
                ],
                [],
                ['expose' => true]
            );
    }

    public function configureDefaultFilterValues(array &$filterValues): void
    {
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name', null)
            ->add('authorName', null, ['hidden' => true]);

        if ($this->showTagTree) {
            $filter->add('tags', CallbackFilter::class, [
                'label_render' => false,
                'label' => false,
                'field_type' => HiddenType::class,
                'callback' => function (
                    ProxyQueryInterface $query,
                    $alias,
                    $field,
                    FilterData $data
                ): bool {
                    if (!$data->hasValue()) {
                        return false;
                    }
                    $query->leftJoin(sprintf('%s.tags', $alias), 't');
                    $query->andWhere('t.id = :id');
                    $query->setParameter('id', $data->getValue());

                    return true;
                },
            ]);
        } else {
            $filter->add('tags');
        }

        $filter->add(
            'context',
            SimpleStringFilter::class,
            [
                'show_filter' => false,
                'operator_type' => ContainsOperatorType::TYPE_EQUAL,
                'case_sensitive' => true,
            ],
            [
                'field_type' => HiddenType::class,
            ]
        )
            ->add(
                'providerName',
                SimpleStringFilter::class,
                [
                    'show_filter' => false,
                    'operator_type' => ContainsOperatorType::TYPE_EQUAL,
                    'case_sensitive' => true,
                ],
                [
                    'field_type' => HiddenType::class,
                ]
            );
    }

    public function getExportFormats(): array
    {
        return [];
    }

    public function setLocalisedMediaProviders(array $providers)
    {
        $this->localisedMediaProviders = $providers;
    }

    /**
     * @param string $name
     *
     * @return string|null
     */
    public function getTemplate($name)
    {
        if (
            'edit' === $name && is_null($this->getSubject()->getId())
            && !$this->getRequest()->query->get('gallery')
        ) {
            $provider = $this->pool->getProvider(
                $this->getRequest()->get('provider')
            );
            if ($provider instanceof FileProvider) {
                return '@NetworkingInitCms/MediaAdmin/multifileupload_jquery.html.twig';
            }
        }

        return $this->getTemplateRegistry()->getTemplate($name);
    }

    /**
     * @return object
     */
    public function checkForDuplicate($media)
    {
        $duplicate = $this->getModelManager()->findOneBy(
            $this->getClass(),
            [
                'context' => $media->getContext(),
                'md5File' => $media->getMd5File(),
            ]
        );

        return $duplicate;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $media = $this->getSubject();

        if (!$media) {
            $media = $this->getNewInstance();
        }

        if (!$media || !$media->getProviderName()) {
            return;
        }

        $form->add('providerName', HiddenType::class);

        $form->getFormBuilder()->addModelTransformer(
            new ProviderDataTransformer(
                $this->pool,
                $this->getClass(),
                ['new_on_update' => false]
            ),
            true
        );

        $provider = $this->pool->getProvider($media->getProviderName());

        if ($media->getId()) {
            $provider->buildEditForm($form);
        } else {
            $provider->buildCreateForm($form);
        }

        if (
            in_array(
                $media->getProviderName(),
                $this->localisedMediaProviders
            )
        ) {
            $form->add(
                'locale',
                ChoiceType::class,
                ['choices' => $this->getLocaleChoices()]
            );
        }

        $transformer = new TagTransformer($this->hasMultipleMediaTags);

        $form->add(
            'tags',
            ModelType::class,
            [
                'required' => false,
                'expanded' => false,
                'multiple' => true,
                'select2' => true,
                'property' => 'adminTitle',
                'help_label' => 'help.media_tag',
                'taggable' => $this->hasMultipleMediaTags,
                'attr' => ['style' => 'width:220px'],
                'transformer' => $transformer,
            ]
        );

        $form->add('providerName', HiddenType::class);
    }

    /**
     * Provide an array of locales where the locale is the key and the label is
     * the value for easy display in a dropdown select for example
     * example: array('de_CH' => 'Deutsch', 'en_GB' => 'English').
     *
     * @return array
     */
    protected function getLocaleChoices()
    {
        $locale = [];

        foreach ($this->languages as $language) {
            $locale[$language['label']] = $language['locale'];
        }

        return $locale;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier(
                'name',
                'string',
                [
                    'template' => '@NetworkingInitCms/MediaAdmin/list_name.html.twig',
                ]
            )
            ->add('createdAt', 'string', ['label' => 'label.created_at'])
            ->add('size', 'string', ['label' => 'label.size']);
    }

    public function alterNewInstance($object): void
    {
        foreach ($this->getExtensions() as $extension) {
            $extension->alterNewInstance($this, $object);
        }

        if ($this->hasRequest()) {
            if (
                $this->getRequest()->isMethod('POST')
                && !$this->getRequest()->get('oneuploader')
            ) {
                $uniqid = $this->getUniqid();
                if (
                    array_key_exists(
                        'providerName',
                        $this->getRequest()->get($uniqid, [])
                    )
                ) {
                    $object->setProviderName(
                        $this->getRequest()->get($uniqid)['providerName']
                    );
                }
            }

            if ($this->getRequest()->get('provider')) {
                $object->setProviderName($this->getRequest()->get('provider'));
            }

            $object->setContext($context = $this->getRequest()->get('context'));
        }
    }

    protected function configurePersistentParameters(): array
    {
        $parameters = [];

        if (!$this->hasRequest()) {
            return $parameters;
        }

        $request = $this->getRequest();

        $filter = $request->query->all('filter');

        $context = $request->query->get('context');
        $provider = $request->query->get('provider');

        if ($request->query->get('providerName')) {
            $provider = $request->query->get('providerName');
        }

        if (is_array($context) && array_key_exists('value', $context)) {
            $context = $context['value'];
        }

        if (!$context && array_key_exists('context', $filter)) {
            $context = $filter['context']['value'];
        }

        if (!$context || '0' === $context) {
            $context = $this->pool->getDefaultContext();
            $request->query->set('context', $context);
        }

        $providers = $this->pool->getProvidersByContext($context);

        // if the context has only one provider, set it into the request
        // so the intermediate provider selection is skipped
        if (1 === \count($providers) && null === $provider) {
            $provider = array_shift($providers)->getName();
            $request->query->set('provider', $provider);
        }

        // if there is a post server error, provider is not posted and in case of
        // multiple providers, it has to be persistent to not being lost
        if (1 < \count($providers) || null !== $provider) {
            $parameters['provider'] = $provider;
        }

        return array_merge($parameters, [
            'context' => $context,
        ]);
    }

    public function configureFilterParameters(array $parameters): array
    {
        if (
            array_key_exists('context', $parameters)
            && !$parameters['context']['value']
        ) {
            $parameters['context']['value'] = $this->pool->getDefaultContext();
        }

        $persistentParameters = $this->getPersistentParameters();

        $context = array_key_exists('context', $persistentParameters) ? $persistentParameters['context'] : '';
        $provider = array_key_exists('provider', $persistentParameters) ? $persistentParameters['provider'] : '';

        if ($context && array_key_exists('context', $parameters)) {
            if ($parameters['context']['value'] != $context) {
                $parameters['context']['value'] = $context;
            }
        } elseif ($context) {
            $parameters['context'] = ['value' => $context];
        }

        if ($provider && array_key_exists('providerName', $parameters)) {
            if ($parameters['providerName']['value'] != $provider) {
                $parameters['providerName']['value'] = $provider;
            }
        } elseif ($provider) {
            $parameters['providerName']['value'] = $provider;
        }

        return $parameters;
    }

    public function configureBatchActions($actions): array
    {
        if (
            $this->hasRequest()
            && !$this->getRequest()->query->get(
                'galleryMode'
            )
        ) {
            if (
                $this->hasRoute('edit') && $this->isGranted('EDIT')
                && $this->hasRoute('delete')
                && $this->isGranted('DELETE')
            ) {
                $actions['add_tags'] = [
                    'label' => 'add_tags',
                    'translation_domain' => $this->getTranslationDomain(),
                    'ask_confirmation' => false,
                ];
            }
        }

        return $actions;
    }
}
