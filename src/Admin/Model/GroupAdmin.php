<?php
/**
 * Created by PhpStorm.
 * User: yorkie
 * Date: 20.12.17
 * Time: 16:38.
 */

namespace Networking\InitCmsBundle\Admin\Model;

use App\Entity\User;
use Networking\InitCmsBundle\Admin\BaseAdmin;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\UserBundle\Form\Type\RolesMatrixType;
use Sonata\UserBundle\Form\Type\SecurityRolesType;
use Sonata\UserBundle\Model\UserInterface;


abstract class GroupAdmin extends BaseAdmin
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
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('name', null, ['field_options' => ['translation_domain' => $this->getTranslationDomain()]])
        ;
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
            ->add('name', null, [ 'layout' => $this->getRequest()->isXmlHttpRequest() ? 'horizontal' : 'inline'])
            ->add(
                'roles',
                RolesMatrixType::class,
                [
                    'multiple' => true,
                    'required' => false,
                    'layout' => $this->getRequest()->isXmlHttpRequest() ? 'horizontal' : 'inline',
                    'excluded_roles' => [
                        UserInterface::ROLE_DEFAULT,
                        UserInterface::ROLE_SUPER_ADMIN,
                        'ROLE_ALLOWED_TO_SWITCH',
                        'ROLE_SONATA_ADMIN',
                        'ROLE_ADMIN'
                    ]
                ]
            );
    }




    /**
     * {@inheritdoc}
     */
    protected $formOptions = [
        'validation_groups' => 'Registration',
    ];


}
