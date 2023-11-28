<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: yorkie
 * Date: 20.12.17
 * Time: 16:38.
 */
namespace Networking\InitCmsBundle\Admin;

use Networking\InitCmsBundle\Admin\BaseAdmin;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\UserBundle\Form\Type\RolesMatrixType;
use Sonata\UserBundle\Form\Type\SecurityRolesType;
use Sonata\UserBundle\Model\UserInterface;

/**
 * Class GroupAdmin.
 */
class GroupAdmin extends BaseAdmin
{
    /**
     * @var array
     */
    protected $trackedActions = ['list'];



    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'cms/user_groups';
    }

    /**
     * @param $trackedActions
     *
     * @return $this
     */
    public function setTrackedActions($trackedActions): AbstractAdmin
    {
        $this->trackedActions = $trackedActions;

        return $this;
    }

    /**
     * @return array
     */
    public function getTrackedActions(): array
    {
        return $this->trackedActions;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name', null, ['field_options' => ['translation_domain' => $this->getTranslationDomain()]])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name');

        $list->add(
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
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name', null, [ 'required' => true, 'row_attr' => ['class' => 'form-floating mb-3']])
            ->add(
                'roles',
                RolesMatrixType::class,
                [
                    'multiple' => true,
                    'required' => false,
                    'layout' => $this->getRequest()->isXmlHttpRequest() ? 'horizontal' : 'inline',
                    'choice_translation_domain' => null,
                    'excluded_roles' => [
                        UserInterface::ROLE_DEFAULT,
                        UserInterface::ROLE_SUPER_ADMIN,
                    ]
                ]
            );
    }


}
