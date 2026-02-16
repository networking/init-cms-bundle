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

use Doctrine\ORM\EntityRepository;
use Networking\InitCmsBundle\Entity\MenuItem;
use Networking\InitCmsBundle\Form\Type\AutocompleteType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelHiddenType;
use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
class MenuItemAdmin extends BaseAdmin
{
    /**
     * The number of result to display in the list.
     *
     * @var int
     */
    protected int $maxPerPage = 10000;

    /**
     * The maximum number of page numbers to display in the list.
     *
     * @var int
     */
    protected int $maxPageLinks = 10000;

    /**
     * @var bool
     */
    protected bool $isRoot = false;

    /**
     * @var array
     */
    protected array $linkTargets
        = [
            '_blank' => '_blank',
            '_self' => '_self',
            '_parent' => '_parent',
            '_top' => '_top',
        ];

    /**
     * @var array
     */
    protected array $trackedActions = ['list'];

    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'cms/menu';
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'admin_networking_initcms_menuitem';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
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

    public function alterNewInstance(object $object): void
    {
        $request = $this->getRequest();

        if (!$locale = $request->query->get('locale')) {
            $locale = $request->getLocale();
        }

        $uniqId = $this->getUniqid();

        $rootId = $request->query->getInt('root_id');

        if ($postArray = $request->request->all($uniqId)) {
            if (array_key_exists('locale', $postArray)) {
                $locale = $postArray['locale'];
            }

            if (array_key_exists('root', $postArray)) {
                $rootId = $postArray['root'];
            }
        }

        if ($rootId) {
            $root = $this->getModelManager()->find($this->getClass(), $rootId);
            $object
                ->setMenu($root)
                ->setRoot($rootId);
            $locale = $root->getLocale();
        }

        $object->setLocale($locale);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $request = $this->getRequest();

        $createRoot = false;
        if ($request->query->get('subclass') && 'menu' === $request->query->get('subclass')
        ) {
            $createRoot = true;
        }

        if ($this->getSubject()->getIsRoot()) {
            $createRoot = true;
        }

        $form
            ->with('general', ['label' => false])
            ->add('name', null, ['layout' => 'horizontal']);

        if ($createRoot) {
            $form
                ->add('description', null, ['layout' => 'horizontal'])
                ->add('isRoot', HiddenType::class, ['data' => true])
                ->add('locale', ChoiceType::class, [
                    'placeholder' => false,
                    'choice_loader' => new CallbackChoiceLoader(
                        fn () => $this->getLocaleChoices()
                    ),
                    'preferred_choices' => [$this->getDefaultLocale()],
                    'translation_domain' => $this->getTranslationDomain()])
                ->end();

            return;
        }
        $form->add('locale', HiddenType::class);

        $form->end();
        // start group page_or_url
        $form
            ->with(
                'form.legend_page_or_url',
                [
                    'label' => 'form.legend_page_or_url',
                    'collapsed' => false,
                    'layout' => 'horizontal',
                ]
            );
        $pageAdmin = $this->getConfigurationPool()->getAdminByAdminCode(
            'networking_init_cms.admin.page'
        );
        $pageClass = $pageAdmin->getClass();
        $locale = $this->getSubject()->getLocale();
        $form
            ->add(
                'page',
                AutocompleteType::class,
                [
                    'attr' => ['placeholder' => '', 'data-dropdown-parent' => '#menu_dialog'],
                    'class' => $pageClass,
                    'required' => false,
                    'layout' => 'horizontal',
                    'choice_label' => 'AdminTitle',
                    'row_attr' => ['class' => 'form-floating mb-3'],
                    'query_builder' => function (EntityRepository $er) use (
                        $locale
                    ) {
                        $qb = $er->createQueryBuilder('p');
                        $qb->where('p.locale = :locale')
                            ->orderBy('p.path', 'asc')
                            ->setParameter(':locale', $locale);

                        return $qb;
                    },
                ]
            );
        $form->add(
            'redirect_url',
            UrlType::class,
            [
                'row_attr' => ['class' => 'form-floating mb-3'],
                'required' => false,
                'help' => 'help.redirect_url',
                'layout' => 'horizontal',
            ]
        );
        $form->add(
            'internal_url',
            TextType::class,
            [
                'row_attr' => ['class' => 'form-floating mb-3'],
                'required' => false,
                'help' => 'help.internal_url',
                'layout' => 'horizontal',
            ]
        );
        $form->end();

        // start group optionals
        $form
            ->with(
                'form.legend_options',
                [
                    'label' => 'form.legend_options',
                    'collapsed' => false,
                    'layout' => 'horizontal',
                ]
            )
            ->add(
                'visibility',
                ChoiceType::class,
                [
                    'row_attr' => ['class' => 'form-floating mb-3'],
                    'layout' => 'horizontal',
                    'help' => 'visibility.helper.text',
                    'choices' => MenuItem::getVisibilityList(),
                    'translation_domain' => $this->getTranslationDomain(),
                ]
            )
            ->add(
                'link_target',
                ChoiceType::class,
                [
                    'row_attr' => ['class' => 'form-floating mb-3'],
                    'layout' => 'horizontal',
                    'choices' => $this->getTranslatedLinkTargets(),
                    'required' => false,
                ]
            )
            ->add(
                'link_class',
                TextType::class,
                [
                    'row_attr' => ['class' => 'form-floating mb-3'],
                    'layout' => 'horizontal',
                    'required' => false,
                ]
            )
            ->add(
                'link_rel',
                TextType::class,
                [
                    'row_attr' => ['class' => 'form-floating mb-3'],
                    'layout' => 'horizontal',
                    'required' => false,
                ]
            )
            ->add(
                'hidden',
                CheckboxType::class,
                ['layout' => 'horizontal', 'required' => false]
            )
            ->add(
                'menu',
                ModelHiddenType::class,
                ['class' => $this->getClass()]
            )
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add(
                'locale',
                CallbackFilter::class,
                ['callback' => $this->getByLocale(...)],
                [
                    'field_type' => LanguageType::class,
                    'field_options' => [
                        'row_attr' => ['class' => 'form-floating'],
                        'placeholder' => false,
                        'choice_loader' => new CallbackChoiceLoader(
                            fn () => $this->getLocaleChoices()
                        ),
                        'preferred_choices' => [$this->getDefaultLocale()],
                        'translation_domain' => $this->getTranslationDomain(),
                    ],
                ]
            );
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
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
    public function getByLocale(
        ProxyQuery $queryBuilder,
        $alias,
        $field,
        FilterData $data,
    ) {
        $locale = $data->hasValue() ? $data->getValue()
            : $this->getDefaultLocale();
        $queryBuilder->where(sprintf('%s.locale = :locale', $alias));
        $queryBuilder->andWhere(
            $queryBuilder->expr()->isNotNull(sprintf('%s.parent', $alias))
        );
        $queryBuilder->setParameter(':locale', $locale);

        return true;
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

    public function preRemove(object $object): void
    {
        if ($object->hasChildren()) {
            throw new ModelManagerException('flash_delete_children_error');
        }
    }
}
