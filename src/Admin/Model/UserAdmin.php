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

use Networking\InitCmsBundle\Form\Type\QrCodeType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\UserBundle\Admin\Model\UserAdmin as BaseUserAdmin;
use Sonata\UserBundle\Form\Type\SecurityRolesType;
use Sonata\UserBundle\Model\UserInterface;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class UserAdmin.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class UserAdmin extends BaseUserAdmin
{
    /**
     * @var string
     */
    protected $baseRoutePattern = 'cms/users';

    /**
     * @var string
     */
    protected $baseRouteName = 'admin_networking_initcms_user';

    /**
     * @var array
     */
    protected $trackedActions = ['list'];

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var bool
     */
    private $googleAuthEnabled = false;

    /**
     * @var \Sonata\UserBundle\GoogleAuthenticator\Helper
     */
    private $googleAuthenticatorHelper;


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


    public function setGoogleAuthenticatorHelper(\Sonata\UserBundle\GoogleAuthenticator\Helper $helper)
    {
        $this->googleAuthenticatorHelper = $helper;
    }


    /**
     * @return array
     */
    public function getTrackedActions()
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
        $securityContext = $this->getConfigurationPool()->getContainer()->get('security.token_storage');
        if ($object == $securityContext->getToken()->getUser()) {
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
        if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $listMapper
                ->add(
                    'impersonating',
                    'string',
                    ['template' => 'NetworkingInitCmsBundle:Admin:Field/impersonating.html.twig']
                );
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
            ->add('username', null, ['field_options' => ['translation_domain' => $this->translationDomain]])
            ->add('email', null, ['hidden' => true])
            ->add('groups', null, ['hidden' => true]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->with('General')
            ->add('username')
            ->add('email')
            ->add('plainPassword', TextType::class, ['required' => (!$this->getSubject() || is_null($this->getSubject()->getId()))])
            ->end()
            ->with('Groups')
            ->add('groups', ModelType::class, [
                'required' => false,
                'expanded' => true,
                'multiple' => true,
                'translation_domain' => false,
            ])
            ->end()
            ->with('Profile')
            ->add('firstname', null, ['required' => false])
            ->add('lastname', null, ['required' => false])
            ->add('locale', LocaleType::class, ['required' => false])
            ->end();

        if (!$this->getSubject()->hasRole('ROLE_SUPER_ADMIN')) {
            $formMapper
                ->with('Management')
                ->add(
                    'realRoles',
                    SecurityRolesType::class,
                    [
                        'expanded' => true,
                        'multiple' => true,
                        'required' => false,
                        'label_render' => false,
                        'label' => false,
                    ]
                )
                ->add('enabled', null, ['required' => false], ['inline_block' => true])
                ->end();
        }

        if($this->googleAuthEnabled){
            $formMapper->with('Keys')
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
