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

use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

/**
 * Class TagAdmin
 * @package Networking\InitCmsBundle\Admin\Model
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class TagAdmin extends Admin
{

    /**
     * Default values to the datagrid.
     *
     * @var array
     */
    protected $datagridValues = array(
        '_page'       => 1,
        '_per_page'   => 25,
        '_sort_by' => 'path',
        '_sort_order'    => 'ASC'
    );

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon-tags';
    }

    /**
     * @param RouteCollection $collection
     */
    public function configureRoutes(RouteCollection $collection)
    {

        parent::configureRoutes($collection);

        $collection->add(
            'update_tree',
            'update_tree',
            array(
                '_controller' => 'NetworkingInitCmsBundle:TagAdmin:updateTree',
            )
        );

        $collection->add(
            'inline_edit',
            'inline_edit',
            array(
                '_controller' => 'NetworkingInitCmsBundle:TagAdmin:inlineEdit',
            )
        );
    }


    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $id = $this->getSubject() ? $this->getSubject()->getId(): null;
        $formMapper
            ->add('name')
            ->add('parent',
                'networking_type_autocomplete',
                array(
                    'help_block' => 'parent.helper.text',
                    'attr' => array('style' => "width:220px"),
                    'property' => 'AdminTitle',
                    'class' => $this->getClass(),
                    'required' => false,
                    'query_builder' => function (EntityRepository $er) use ($id)  {
                        $qb = $er->createQueryBuilder('t');
                        $qb->orderBy('t.path', 'asc');
                        if($id){
                            $qb->where('t.id != :id')
                                ->setParameter(':id', $id);
                        }

                        return $qb;
                    },
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('path');
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
                '_action',
                'actions',
                array(
                    'label' => ' ',
                    'actions' => array(
                        'edit' => array(),
                        'delete' => array()
                    )
                )
            );
    }
}
