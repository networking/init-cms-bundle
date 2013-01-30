<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Admin;

use Networking\InitCmsBundle\Admin\BaseAdmin,
    Sonata\AdminBundle\Admin\Admin,
    Sonata\AdminBundle\Datagrid\ListMapper,
    Sonata\AdminBundle\Datagrid\DatagridMapper,
    Sonata\AdminBundle\Validator\ErrorElement,
    Sonata\AdminBundle\Form\FormMapper,
    Sonata\AdminBundle\Route\RouteCollection,
    Doctrine\ORM\EntityRepository,
    Networking\InitCmsBundle\Form\DataTransformer\MenuItemToNumberTransformer;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MenuItemAdmin extends BaseAdmin
{
    /**
     * @var string
     */
    protected $baseRoutePattern = 'cms/menu';

    /**
     * @var bool
     */
    protected $isRoot = false;

    /**
     * @var array $linkTargets
     */
    protected $linkTargets = array('_blank' => '_blank', '_self' => '_self', '_parent' => '_parent', '_top' => '_top');


    /**
     * @param \Sonata\AdminBundle\Route\RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('createFromPage', 'create_from_page/root_id/{rootId}/page_id/{pageId}', array(), array('_method' => 'GET|POST', 'rootId', 'pageId'));
        $collection->add('ajaxController', 'ajax_navigation', array(), array('_method' => 'GET|POST'));
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        if (!$locale = $this->getRequest()->get('locale')) {
            $locale = $this->getRequest()->getLocale();
        }

        $uniqId = $this->getUniqid();

        if($postArray = $this->getRequest()->get($uniqId)){
           if(array_key_exists('locale', $postArray)){
               $locale = $postArray['locale'];
           }
        }


        $er = $this->container->get('Doctrine')->getRepository('NetworkingInitCmsBundle:MenuItem');
        $id = $this->getRequest()->get('id');

        if ($id) {
            $menuItem = $er->find($id);
            $locale = $menuItem->getLocale();
        }

        if ($rootId = $this->getRequest()->get('root_id')) {
            $root = $er->find($rootId);
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
            $formMapper->add('isRoot', 'hidden', array('data' => true));
        } else {
//            $formMapper->add('menu', 'hidden', array('data' => $root->getId()));
            // start group page_or_url
            $formMapper
                ->with('Target (page or url)',
                    array(
                        'collapsed' => false,
                        'description' => $this->translator->trans('form.legend_page_or_url', array(), $this->translationDomain)
                    )
                );
            $formMapper
                ->add('page',
                'networking_type_autocomplete',
                    array(
                        'attr' => array('style' => "width:220px"),
                        'class' => 'Networking\InitCmsBundle\Entity\Page',
                        'required' => false,
                        'property' => 'AdminTitle',
                        'query_builder' => function (EntityRepository $er) use ($locale) {
                            $qb = $er->createQueryBuilder('p');

                            return $qb->where('p.locale = :locale')
                                ->orderBy('p.path', 'asc')
                                ->setParameter(':locale', $locale);
                        },
                    )
            );
            $formMapper->add('redirect_url', 'url', array('required'=>false));
            $formMapper->end();

            // start group optionals
            $formMapper
                ->with('Options',
                    array(
                        'collapsed' => true,
                        'description' => $this->translator->trans('form.legend_options', array(), $this->translationDomain)
                    ))
                ->add('link_target', 'choice', array('choices'=>$this->getTranslatedLinkTargets(), 'required'=>false))
                ->add('link_class', 'text', array('required'=>false))
                ->add('link_rel', 'text', array('required'=>false))
                ->end();

            $entityManager = $this->container->get('Doctrine')->getEntityManager();

            $transformer = new MenuItemToNumberTransformer($entityManager);

            $menuField = $formMapper->getFormBuilder()->create('menu', 'hidden',  array('data' => $root, 'data_class' => null));
            $menuField->addModelTransformer($transformer);
            $formMapper
                    ->add($menuField, 'hidden');

        }
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {

        $datagridMapper
            ->add('locale',
            'doctrine_orm_callback',
            array('callback' => array($this, 'getByLocale')),
            'choice',
            array(
                'empty_value' => false,
                'choices' => $this->getLocaleChoices(),
                'preferred_choices' => array($this->getDefaultLocale())
            ));
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('path')
            ->add('page', 'many_to_one', array('template' => 'NetworkingInitCmsBundle:PageAdmin:page_list_field.html.twig'))
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
    public function getByLocale(\Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery $queryBuilder, $alias, $field, $value)
    {
        if (!$locale = $value['value']) {
            $locale = $this->getDefaultLocale();
        }


        $queryBuilder->where(sprintf('%s.locale = :locale', $alias));
        $queryBuilder->andWhere($queryBuilder->expr()->isNotNull(sprintf('%s.parent', $alias)));
        $queryBuilder->setParameter(':locale', $locale);

        return true;
    }

    /**
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param mixed                                      $object
     */
    public function validate(ErrorElement $errorElement, $object)
    {
        $errorElement
            ->with('name')
            ->assertNotNull(array())
            ->assertNotBlank()
            ->end();

        if(!$object->getIsRoot()){
            if (!$object->getRedirectUrl() AND !$object->getPage()) {
                $errorElement
                    ->with('menu_page_or_url_required')
                    ->addViolation($this->translator->trans('menu.page_or_url.required', array(), $this->translationDomain))
                    ->end();
            }

        }
    }

    /**
     * @param $isRoot
     */
    public function setIsRoot($isRoot)
    {
        $this->isRoot = $isRoot;
    }

    /**
     * @param string $name
     * @return null|string
     */
    public function getTemplate($name)
    {
        switch ($name) {
            case 'list':
                return 'NetworkingInitCmsBundle:MenuItemAdmin:menu_list.html.twig';
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
        foreach($this->linkTargets as $key => $value)
        {
            $translatedLinkTargets[$key] = $this->translator->trans($value, array(), $this->translationDomain);
        }
        return $translatedLinkTargets;
    }
}
