<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

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
                    ]
                ]
            );
    }


}
