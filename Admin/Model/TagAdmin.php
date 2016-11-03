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
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $id = $this->getSubject() ? $this->getSubject()->getId(): null;
        $formMapper
            ->add('name')
            ->add(
                    'parent',
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
            ->add(
                'path',
                'doctrine_orm_callback',
                array('callback' => array($this, 'matchPath'), 'hidden' => true)
            );
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

        $qb->where(sprintf('%s.%s LIKE :path', $alias, $fieldName));
        $qb->setParameter(':path', '%' . $data['value'] . '%');

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('adminTitle')
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

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, $object)
    {
        $errorElement
            ->with('name')
            ->assertNotNull(array())
            ->assertNotBlank()
            ->assertLength(array('max' => 255))
            ->end();

    }
}
