<?php
/**
 * Created by PhpStorm.
 * User: yorkie
 * Date: 20.12.17
 * Time: 16:38.
 */

namespace Networking\InitCmsBundle\Admin\Model;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\UserBundle\Admin\Entity\GroupAdmin as SonataGroupAdmin;
use Sonata\UserBundle\Form\Type\SecurityRolesType;

abstract class GroupAdmin extends SonataGroupAdmin
{
    /**
     * @var array
     */
    protected $trackedActions = ['list'];

    /**
     * @param $trackedActions
     *
     * @return $this
     */
    public function setTrackedActions($trackedActions)
    {
        $this->trackedActions = $trackedActions;

        return $this;
    }

    /**
     * @return array
     */
    public function getTrackedActions()
    {
        return $this->trackedActions;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('name');

        $listMapper->add(
            '_action',
            'actions',
            [
                'label' => ' ',
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ]
        );
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon-group';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('name')
            ->add(
                'roles',
                SecurityRolesType::class,
                [
                    'expanded' => true,
                    'multiple' => true,
                    'required' => false,
                ]
            );
    }
}
