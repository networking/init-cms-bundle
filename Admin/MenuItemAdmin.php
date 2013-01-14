<?php

/*
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
    Doctrine\ORM\EntityRepository;

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
            ->add('name');

        $formMapper->add('locale', 'hidden', array('data' => $locale));

        if ($this->isRoot) {
            $formMapper->add('isRoot', 'hidden', array('data' => true));
        } else {
            $formMapper
                ->add('page',
                'networking_type_autocomplete',
                array(
                    'attr' => array('style' => "width:220px"),
                    'class' => 'Networking\InitCmsBundle\Entity\Page',
                    'required' => true,
                    'property' => 'AdminTitle',
                    'query_builder' => function (EntityRepository $er) use ($locale) {
                        $qb = $er->createQueryBuilder('p');

                        return $qb->where('p.locale = :locale')
                            ->orderBy('p.path', 'asc')
                            ->setParameter(':locale', $locale);
                    },
                )
            )
                ->add('menu',
                'entity',
                array(
                    'class' => 'Networking\InitCmsBundle\Entity\MenuItem',
                    'required' => true,
                    'preferred_choices' => array($root),
                    'query_builder' => function (EntityRepository $er) use ($locale) {
                        $qb = $er->createQueryBuilder('m');

                        return $qb->where('m.isRoot = 1')
                            ->andWhere('m.locale = :locale')
                            ->orderBy('m.name')
                            ->setParameter(':locale', $locale);
                    }
                )
            );
        }
//                ->add('path', 'text', array('read_only' => true));
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
            ->assertMaxLength(array('limit' => 255))
            ->end();
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

}
