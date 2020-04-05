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
use Networking\InitCmsBundle\Entity\MenuItem;
use Networking\InitCmsBundle\Form\DataTransformer\ModelToIdTransformer;
use Networking\InitCmsBundle\Form\Type\AutocompleteType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

/**
 * Class MenuItemAdmin.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class MenuItemAdmin extends BaseAdmin
{
    /**
     * @var string
     */
    protected $baseRoutePattern = 'cms/menu';

    /**
     * @var string
     */
    protected $baseRouteName = 'admin_networking_initcms_menuitem';

    /**
     * The number of result to display in the list.
     *
     * @var int
     */
    protected $maxPerPage = 10000;


    /**
     * The maximum number of page numbers to display in the list.
     *
     * @var int
     */
    protected $maxPageLinks = 10000;

    /**
     * @var bool
     */
    protected $isRoot = false;

    /**
     * @var array
     */
    protected $linkTargets = ['_blank' => '_blank', '_self' => '_self', '_parent' => '_parent', '_top' => '_top'];

    /**
     * @var array
     */
    protected $trackedActions = ['list'];

    protected $formOptions =['layout' => 'horizontal'];

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon-align-left';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add(
            'createFromPage',
            'create_from_page/root_id/{rootId}/page_id/{pageId}',
            [],
            [
                '_controller' => 'Networking\InitCmsBundle\Controller\MenuItemAdminController::createFromPageAction',
                '_method' => 'GET|POST',
                'rootId',
                'pageId',
            ]
        );
        $collection->add(
            'ajaxController',
            'ajax_navigation',
            [],
            [

                '_controller' => 'Networking\InitCmsBundle\Controller\MenuItemAdminController::ajaxControllerAction',
                '_method' => 'GET|POST',
            ]
        );
        $collection->add(
            'newPlacement',
            'new_placement/{newMenuItemId}/{menuItemId}',
            [],
            [
                '_controller' => 'Networking\InitCmsBundle\Controller\MenuItemAdminController::newPlacementAction',
                '_method' => 'GET|POST',
                'newMenuItemId',
                'menuItemId',
            ]
        );
        $collection->add(
            'placement',
            'placement',
            [],
            [
                '_controller' => 'Networking\InitCmsBundle\Controller\MenuItemAdminController::placementAction',
                '_method' => 'GET|POST',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        if (!$locale = $this->getRequest()->get('locale')) {
            $locale = $this->getRequest()->getLocale();
        }

        $uniqId = $this->getUniqid();

        if ($postArray = $this->getRequest()->get($uniqId)) {
            if (array_key_exists('locale', $postArray)) {
                $locale = $postArray['locale'];
            }
        }

        $id = $this->getRequest()->get('id');

        if ($id) {
            $menuItem = $this->getModelManager()->find($this->getClass(), $id);
            $locale = $menuItem->getLocale();
        }

        if ($rootId = $this->getRequest()->get('root_id')) {
            $root = $this->getModelManager()->find($this->getClass(), $rootId);
        } elseif ($id) {
            $root = $this->getModelManager()->find($this->getClass(), $this->getSubject()->getRoot());
        } else {
            $root = $this->getModelManager()->findOneBy($this->getClass(), ['isRoot' => 1, 'locale' => $locale]);
        }

        if ($this->getRequest()->get('subclass') && $this->getRequest()->get('subclass') == 'menu') {
            $this->isRoot = true;
        } elseif ($this->getSubject()->getIsRoot()) {
            $this->isRoot = true;
        }

        $formMapper
            ->with('general',['label' => false])
            ->add('locale', HiddenType::class, ['data' => $locale])
            ->add('name', null, ['layout' => 'horizontal']);

        if ($this->isRoot) {
            $formMapper
                ->add('description', null, ['layout' => 'horizontal'])
                ->add('isRoot', HiddenType::class, ['data' => true])
                ->end();
        } else {
            $formMapper->end();
            // start group page_or_url
            $formMapper
                ->with(
                    'form.legend_page_or_url',
                    [
                        'collapsed' => false,
                        'layout' => 'horizontal',
                    ]
                );
            $pageAdmin = $this->configurationPool->getAdminByAdminCode('networking_init_cms.admin.page');
            $pageClass = $pageAdmin->getClass();

            $formMapper
                ->add(
                    'page',
                    AutocompleteType::class,
                    [
                        'attr' => ['style' => 'width:220px'],
                        'class' => $pageClass,
                        'required' => false,
                        'layout' => 'horizontal',
                        'choice_label' => 'AdminTitle',
                        'query_builder' => function (EntityRepository $er) use ($locale) {
                            $qb = $er->createQueryBuilder('p');
                            $qb->where('p.locale = :locale')
                                ->orderBy('p.path', 'asc')
                                ->setParameter(':locale', $locale);

                            return $qb;
                        },
                    ]
                );
            $formMapper->add(
                'redirect_url',
                UrlType::class,
                ['required' => false, 'help_block' => 'help.redirect_url', 'layout' => 'horizontal']
            );
            $formMapper->add(
                'internal_url',
                TextType::class,
                ['required' => false, 'help_block' => 'help.internal_url', 'layout' => 'horizontal']
            );
            $formMapper->end();

            // start group optionals
            $formMapper
                ->with(
                    'form.legend_options',
                    [
                        'collapsed' => false,
                        'layout' => 'horizontal',
                    ]
                )
                ->add(
                    'visibility',
                    ChoiceType::class,
                    [
                        'layout' => 'horizontal',
                        'help_block' => 'visibility.helper.text',
                        'choices' => MenuItem::getVisibilityList(),
                        'translation_domain' => $this->translationDomain,
                    ]
                )
                ->add(
                    'link_target',
                    ChoiceType::class,
                    [
                        'layout' => 'horizontal',
                        'choices' => $this->getTranslatedLinkTargets(),
                        'required' => false,
                    ]
                )
                ->add('link_class', TextType::class, ['layout' => 'horizontal', 'required' => false])
                ->add('link_rel', TextType::class, ['layout' => 'horizontal', 'required' => false])
                ->add('hidden', null, ['layout' => 'horizontal', 'required' => false])
                ->end();

            $transformer = new ModelToIdTransformer($this->getModelManager(), $this->getClass());

            $menuField = $formMapper->getFormBuilder()->create(
                'menu',
                HiddenType::class,
                ['data' => $root, 'data_class' => null]
            )->addModelTransformer($transformer);
            $formMapper->add($menuField, HiddenType::class);


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
                ['callback' => [$this, 'getByLocale']],
                LanguageType::class,
                [
                    'placeholder' => false,
                    'choice_loader' => new CallbackChoiceLoader(
                        function () {
                            return $this->getLocaleChoices();
                        }
                    ),
                    'preferred_choices' => [$this->getDefaultLocale()],
                    'translation_domain' => $this->translationDomain,
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('path')
            ->add(
                'page',
                'many_to_one',
                ['template' => '@NetworkingInitCms/PageAdmin/page_list_field.html.twig']
            )
            ->add('redirect_url')
            ->add('link_target')
            ->add('link_class')
            ->add('link_rel')
            ->add('locale')
            ->add('menu');
    }

    /**
     * @param array $filterValues
     */
    public function configureDefaultFilterValues(array &$filterValues)
    {
        $filterValues['locale'] = [
            'type' => \Sonata\AdminBundle\Form\Type\Filter\ChoiceType::TYPE_EQUAL,
            'value' => $this->getDefaultLocale(),
        ];
    }

    /**
     * @param $queryBuilder
     * @param $alias
     * @param $field
     * @param $value
     *
     * @return bool
     */
    public function getByLocale(
        \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery $queryBuilder,
        $alias,
        $field,
        $value
    ) {
        if (!$locale = $value['value']) {
            $locale = $this->getDefaultLocale();
        }
        $queryBuilder->where(sprintf('%s.locale = :locale', $alias));
        $queryBuilder->andWhere($queryBuilder->expr()->isNotNull(sprintf('%s.parent', $alias)));
        $queryBuilder->setParameter(':locale', $locale);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, $object)
    {
        $errorElement
            ->with('name')
            ->assertNotBlank()
            ->end();

        if (!$object->getIsRoot()) {
            if (!$object->getRedirectUrl() and !$object->getPage() and !$object->getInternalUrl()) {
                $errorElement
                    ->with('menu_page_or_url_required')
                    ->addViolation('menu.page_or_url.required')
                    ->end();
            }
        }
    }

    /**
     * @param bool $isRoot
     */
    public function setIsRoot($isRoot)
    {
        $this->isRoot = $isRoot;
    }



    /**
     * returns all translated link targets.
     *
     * @return array
     */
    public function getTranslatedLinkTargets()
    {
        $translatedLinkTargets = [];
        foreach ($this->linkTargets as $key => $value) {
            $translatedLinkTargets[$key] = $value;
        }

        return $translatedLinkTargets;
    }

    /**
     * {@inheritdoc}
     */
    public function preRemove($object)
    {
        if ($object->hasChildren()) {
            throw new ModelManagerException('flash_delete_children_error');
        }
    }
}
