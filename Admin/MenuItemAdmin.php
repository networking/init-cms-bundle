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

class MenuItemAdmin extends BaseAdmin
{

    protected $isRoot = false;

    /**
     * @param \Sonata\AdminBundle\Route\RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {

        $collection->add('editMenu', '{id}/edit_menu');
        $collection->add('createMenu', 'create_menu');
        $collection->add('ajaxNavigation', 'ajax_navigation', array(), array('_method' => 'GET|POST'));
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $locale = $this->getRequest()->getLocale();

        $repository = $this->container->get('Doctrine')->getRepository('NetworkingInitCmsBundle:MenuItem');
        $id = $this->getRequest()->get('id');

        if ($id) {
            $menuItem = $repository->find($id);
            $locale = $menuItem->getLocale();
        }


        $formMapper
            ->add('name');

        $formMapper->add('locale', 'hidden', array('data' => $locale));

        if($this->isRoot){
           $formMapper->add('isRoot', 'hidden', array('data' => true));
        }else {
            $formMapper
                ->add('page',
                'entity',
                array(
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
        $locale = $this->getRequest()->getLocale();

        if ($filter = $this->getRequest()->get('filter')) {
            $locale = $filter['locale']['value'];
        }

        $datagridMapper
            ->add('name')
            ->add('page', null, array(),
            'entity',
            array(
                'class' => 'Networking\InitCmsBundle\Entity\Page',
                'property' => 'AdminTitle',
                'query_builder' => function (EntityRepository $er) use ($locale) {
                    $qb = $er->createQueryBuilder('p');
                    $qb->where('p.locale = :locale')
                        ->orderBy('p.path', 'asc');
                    $qb->setParameter(':locale', $locale);

                    return $qb;
                },
            ))
            ->add('locale',
            'doctrine_orm_callback',
            array('callback' => array($this, 'getByLocale')),
            'choice',
            array(
                'empty_value' => false,
                'choices' => $this->getLocaleChoices(),
                'preferred_choices' => array($this->getRequest()->getLocale())
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
            ->add('page', 'many_to_one', array('template' => 'NetworkingInitCmsBundle:CRUD:page_list_field.html.twig'))
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
    public function getByLocale($queryBuilder, $alias, $field, $value)
    {
        if (!$locale = $value['value']) {
            $locale = $this->getRequest()->getLocale();
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

    public function setIsRoot($isRoot)
    {
        $this->isRoot = $isRoot;
    }

}
