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
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Doctrine\ORM\EntityRepository;
use Networking\InitCmsBundle\Form\DataTransformer\MenuItemToNumberTransformer;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Doctrine\ORM\QueryBuilder;

/**
 * Class MenuItemAdmin
 * @package Networking\InitCmsBundle\Admin\Model
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class MenuItemAdmin extends BaseAdmin
{
    /**
     * @var string
     */
    protected $baseRoutePattern = 'cms/menu';
    
    /**
     * The number of result to display in the list
     *
     * @var integer
     */
    protected $maxPerPage = 10000;

    /**
     * The maximum number of page numbers to display in the list
     *
     * @var integer
     */
    protected $maxPageLinks = 10000;

    /**
     * @var bool
     */
    protected $isRoot = false;

    /**
     * @var array $linkTargets
     */
    protected $linkTargets = array('_blank' => '_blank', '_self' => '_self', '_parent' => '_parent', '_top' => '_top');

    /**
     * @var array
     */
    protected $trackedActions = array('list');

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'icon-align-left';
    }


    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add(
            'createFromPage',
            'create_from_page/root_id/{rootId}/page_id/{pageId}',
            array(),
            array('_method' => 'GET|POST', 'rootId', 'pageId')
        );
        $collection->add('ajaxController', 'ajax_navigation', array(), array('_method' => 'GET|POST'));
        $collection->add(
            'newPlacement',
            'new_placement/{newMenuItemId}/{menuItemId}',
            array(),
            array('_method' => 'GET|POST', 'newMenuItemId', 'menuItemId')
        );
        $collection->add(
            'placement',
            'placement',
            array(),
            array('_method' => 'GET|POST')
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


        $er = $this->getContainer()->get('Doctrine')->getRepository('NetworkingInitCmsBundle:MenuItem');
        $id = $this->getRequest()->get('id');

        if ($id) {
            $menuItem = $er->find($id);
            $locale = $menuItem->getLocale();
        }

        if ($rootId = $this->getRequest()->get('root_id')) {
            $root = $er->find($rootId);
        } elseif ($id) {
            $root = $er->find($this->getSubject()->getRoot());
        } else {
            $root = $er->findOneBy(array('isRoot' => 1, 'locale' => $locale));
        }

        if ($this->getRequest()->get('subclass') && $this->getRequest()->get('subclass') == 'menu') {
            $this->isRoot = true;
        } elseif ($this->getSubject()->getIsRoot()) {
            $this->isRoot = true;
        }

        $formMapper
            ->add('locale', 'hidden', array('data' => $locale))
            ->add('name');


        if ($this->isRoot) {
            $formMapper
                ->add('description')
                ->add('isRoot', 'hidden', array('data' => true))
                ->end();
        } else {
            // start group page_or_url
            $formMapper
                ->with(
                    'form.legend_page_or_url',
                    array(
                        'collapsed' => false,
                        'description' => $this->translator->trans(
                                'form.legend_page_or_url',
                                array(),
                                $this->translationDomain
                            )
                    )
                );
            $pageAdmin = $this->configurationPool->getAdminByAdminCode('networking_init_cms.admin.page');
            $pageClass = $pageAdmin->getClass();

            $formMapper
                ->add(
                    'page',
                    'networking_type_autocomplete',
                    array(
                        'attr' => array('style' => "width:220px"),
                        'class' => $pageClass,
                        'required' => false,
                        'property' => 'AdminTitle',
                        'query_builder' => function (EntityRepository $er) use ($locale) {
                                $qb = $er->createQueryBuilder('p');

                                $qb->where('p.locale = :locale')
                                    ->orderBy('p.path', 'asc')
                                    ->setParameter(':locale', $locale);

                                return $qb;
                            },
                    )
                );
            $formMapper->add('redirect_url', 'url', array('required' => false, 'help_inline' => 'help.redirect_url'));
            $formMapper->add('internal_url', 'text', array('required' => false, 'help_inline' => 'help.internal_url'));
            $formMapper->end();

            // start group optionals
            $formMapper
                ->with(
                    'Options',
                    array(
                        'collapsed' => false,
                        'description' => $this->translator->trans(
                                'form.legend_options',
                                array(),
                                $this->translationDomain
                            )
                    )
                )
                ->add(
                    'visibility',
                    'sonata_type_translatable_choice',
                    array(
                        'help_inline' => 'visibility.helper.text',
                        'choices' => MenuItem::getVisibilityList(),
                        'catalogue' => $this->translationDomain
                    )
                )
                ->add(
                    'link_target',
                    'choice',
                    array('choices' => $this->getTranslatedLinkTargets(), 'required' => false)
                )
                ->add('link_class', 'text', array('required' => false))
                ->add('link_rel', 'text', array('required' => false))
                ->add('hidden', null, array('required' => false))
                ->end();

            $entityManager = $this->getContainer()->get('Doctrine')->getManager();

            $transformer = new MenuItemToNumberTransformer($entityManager);

            $menuField = $formMapper->getFormBuilder()->create(
                'menu',
                'hidden',
                array('data' => $root, 'data_class' => null)
            );
            $menuField->addModelTransformer($transformer);
            $formMapper
                ->add($menuField, 'hidden');
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
                array('callback' => array($this, 'getByLocale')),
                'choice',
                array(
                    'empty_value' => false,
                    'choices' => $this->getLocaleChoices(),
                    'preferred_choices' => array($this->getDefaultLocale())
                )
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
                array('template' => 'NetworkingInitCmsBundle:PageAdmin:page_list_field.html.twig')
            )
            ->add('redirect_url')
            ->add('link_target')
            ->add('link_class')
            ->add('link_rel')
            ->add('locale')
            ->add('menu');
    }

    /**
     * @param $queryBuilder
     * @param $alias
     * @param $field
     * @param $value
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
            if (!$object->getRedirectUrl() AND !$object->getPage() AND !$object->getInternalUrl()) {
                $errorElement
                    ->with('menu_page_or_url_required')
                    ->addViolation(
                        $this->translator->trans('menu.page_or_url.required', array(), $this->translationDomain)
                    )
                    ->end();
            }

        }
    }

    /**
     * @param boolean $isRoot
     */
    public function setIsRoot($isRoot)
    {
        $this->isRoot = $isRoot;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate($name)
    {
        switch ($name) {
            case 'list':
                return 'NetworkingInitCmsBundle:MenuItemAdmin:menu_list.html.twig';
                break;
            case 'placement':
                return 'NetworkingInitCmsBundle:MenuItemAdmin:placement.html.twig';
                break;
            default:
                return parent::getTemplate($name);
        }
    }

    /**
     * returns all translated link targets
     * @return array
     */
    public function getTranslatedLinkTargets()
    {
        $translatedLinkTargets = array();
        foreach ($this->linkTargets as $key => $value) {
            $translatedLinkTargets[$key] = $this->translator->trans($value, array(), $this->translationDomain);
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
