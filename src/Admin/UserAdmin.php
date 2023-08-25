<?php

declare(strict_types=1);

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Admin;

use Networking\InitCmsBundle\Entity\Group;
use Networking\InitCmsBundle\Form\Type\QrCodeType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\UserBundle\Admin\Model\UserAdmin as BaseUserAdmin;
use Sonata\UserBundle\Form\Type\RolesMatrixType;
use Sonata\UserBundle\Form\Type\SecurityRolesType;
use Sonata\UserBundle\Model\UserInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class UserAdmin.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class UserAdmin extends BaseUserAdmin
{

    /**
     * @var array
     */
    protected $trackedActions = ['list'];

    private ?\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage = null;

    private bool $googleAuthEnabled = false;

    private ?\Networking\InitCmsBundle\GoogleAuthenticator\Helper $googleAuthenticatorHelper = null;

    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'cms/users';
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'admin_networking_initcms_user';
    }

    /**
     * @param TokenStorageInterface $container
     * @return void
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function setGoogleAuthEnabled(bool $googleAuthEnabled)
    {
        $this->googleAuthEnabled = $googleAuthEnabled;
    }


    public function setGoogleAuthenticatorHelper(\Networking\InitCmsBundle\GoogleAuthenticator\Helper $helper)
    {
        $this->googleAuthenticatorHelper = $helper;
    }


    /**
     * @return array
     */
    public function getTrackedActions(): array
    {
        return $this->trackedActions;
    }

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
    public function getSubNavLinks()
    {
        $pool = $this->getConfigurationPool();
        $groupAdmin = $pool->getAdminByAdminCode('sonata.user.admin.group');

        $links = [
            $this->trans($this->getLabel()) => $this,
            $groupAdmin->trans($groupAdmin->getLabel()) => $groupAdmin,

        ];

        return $links;
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate($object): void
    {
        if ($object == $this->tokenStorage->getToken()->getUser()) {
            $this->getRequest()->getSession()->set('admin/_locale', $object->getLocale());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('username')
            ->add('email')
            ->add('groups', null, ['template' => '@NetworkingInitCms/CRUD/list_orm_many_to_many.html.twig', 'route' => ['name' => 'edit']])
            ->add(
                'enabled',
                null,
                ['editable' => true]
            );
        if($this->googleAuthEnabled){
            $listMapper->add('hasStepVerificationCode', 'boolean');
        }
        if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $listMapper
                ->add('impersonating', FieldDescriptionInterface::TYPE_STRING, [
                    'virtual_field' => true,
                    'template' => '@NetworkingInitCms/Admin/Field/impersonating.html.twig'
                ]);
        }

        $listMapper->add(
            '_action',
            'actions',
            [
                'label' => false,
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
    protected function configureDatagridFilters(DatagridMapper $filterMapper): void
    {
        $filterMapper
            ->add('username', null, ['field_options' => ['translation_domain' => $this->getTranslationDomain()]])
            ->add('email', null, ['hidden' => true])
            ->add('groups', null, ['hidden' => true]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {

        $formMapper
            ->with('General', ['class' => 'col-md-6'])
            ->add('username')
            ->add('email')
            ->add('plainPassword', TextType::class, ['required' => (!$this->getSubject() || is_null($this->getSubject()->getId()))])
            ->end()
            ->with('Profile', ['class' => 'col-md-6'])
            ->add('firstname', null, ['required' => false])
            ->add('lastname', null, ['required' => false])
            ->add('locale', LocaleType::class, ['required' => false])
            ->end()
            ->with('Groups', ['class' => 'col-md-3'])
            ->add('groups', ModelType::class, [
                'class' => Group::class,
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ])
            ->end()

        ;

        if (!$this->getSubject()->hasRole('ROLE_SUPER_ADMIN')) {
            $formMapper
                ->with('Management', ['class' => 'col-md-9'])
                ->add(
                    'realRoles',
                    RolesMatrixType::class,
                    [
                        'multiple' => true,
                        'required' => false,
                        'label' => false,
                        'choice_translation_domain' => null,
                        'excluded_roles' => [
                            UserInterface::ROLE_DEFAULT,
                            UserInterface::ROLE_SUPER_ADMIN,
                        ]
                    ]
                )
                ->add('enabled', CheckboxType::class, ['label_render' => true, 'widget_checkbox_label' => 'widget', 'label_attr' => ['class' => 'checkbox-line']])
                ->end();
        }

        if($this->googleAuthEnabled){
            $formMapper->with('Keys', ['label' => false])
                ->add('twoStepVerificationCode', null, ['required' => false, 'disabled' => true])
                ->end();


            if ($this->getSubject() == $this->tokenStorage->getToken()->getUser()) {

                $user = $this->tokenStorage->getToken()->getUser();

                if($user instanceof UserInterface && $user->getTwoStepVerificationCode()){
                    $qrCodeUrl = $this->googleAuthenticatorHelper->getUrl($user);

                    $formMapper->with('Keys')
                        ->add('qrCode', QrCodeType::class, ['required' => false, 'mapped' => false, 'qrCodeUrl' => $qrCodeUrl])
                        ->end();
                }
            }
        }
    }
}