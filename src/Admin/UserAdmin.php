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
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\UserBundle\Admin\Model\UserAdmin as BaseUserAdmin;
use Sonata\UserBundle\Form\Type\RolesMatrixType;
use Sonata\UserBundle\Model\UserInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Intl\Locales;
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

    protected array $languages = [];

    private ?\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage = null;

    private bool $googleAuthEnabled = false;

    private ?\Networking\InitCmsBundle\GoogleAuthenticator\Helper $googleAuthenticatorHelper = null;

    public function configureRoutes(RouteCollectionInterface $collection): void
    {

        $collection->add(
            'profile_security',
            'profile/security',
            options: ['methods' => ['GET', 'POST']]
        );

        $collection->add(
            'profile_security_create_authenticator',
            'profile/create-authenticator',
            options: ['methods' => ['GET', 'POST'], 'expose' => true]
        );

        $collection->add(
            'profile_security_verify_authenticator',
            'profile/verify-authenticator',
            options: ['methods' => ['GET', 'POST'], 'expose' => true]
        );

        $collection->add(
            'profile_security_get_authenticator',
            'profile/get-authenticator',
            options: ['methods' => ['GET', 'POST'], 'expose' => true]
        );

        $collection->add(
            'get_webauthn_keys',
            'auth/get-keys',
            options: ['methods' => ['GET', 'POST'], 'expose' => true]
        );

        $collection->add(
            'rename_webauthn_key',
            'auth/rename-key',
            options: ['methods' => ['GET', 'POST'], 'expose' => true]
        );

        $collection->add(
            'remove_webauthn_key',
            'auth/remove-key',
            options: ['methods' => ['GET', 'POST'], 'expose' => true]
        );


        parent::configureRoutes(
            $collection
        ); // TODO: Change the autogenerated stub
    }

    public function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::PER_PAGE] = 1000000;
    }

    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'cms/users';
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'admin_networking_initcms_user';
    }

    /**
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

    public function setGoogleAuthenticatorHelper(
        \Networking\InitCmsBundle\GoogleAuthenticator\Helper $helper
    ) {
        $this->googleAuthenticatorHelper = $helper;
    }

    public function getTrackedActions(): array
    {
        return $this->trackedActions;
    }

    /**
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

    public function postUpdate($object): void
    {
        if ($object == $this->tokenStorage->getToken()->getUser()) {
            $this->getRequest()->getSession()->set(
                'admin/_locale',
                $object->getLocale()
            );
        }
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('username')
            ->add('email')
            ->add(
                'groups',
                null,
                [
                    'template' => '@NetworkingInitCms/CRUD/list_orm_many_to_many.html.twig',
                    'route' => ['name' => 'edit'],
                ]
            )
            ->add(
                'enabled',
                null,
                ['editable' => true]
            );
        if ($this->googleAuthEnabled) {
            $list->add('hasStepVerificationCode', 'boolean');
        }
        if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $list
                ->add('impersonating', FieldDescriptionInterface::TYPE_STRING, [
                    'virtual_field' => true,
                    'template' => '@NetworkingInitCms/Admin/Field/impersonating.html.twig',
                ]);
        }

        $list->add(
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

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add(
                'username',
                null,
                [
                    'field_options' => [
                        'row_attr' => ['class' => 'form-floating'],
                        'translation_domain' => $this->getTranslationDomain(),
                    ],
                ]
            )
            ->add(
                'email',
                null,
                ['field_options' => ['row_attr' => ['class' => 'form-floating']]]
            )
            ->add(
                'groups',
                null,
                ['field_options' => ['row_attr' => ['class' => 'form-floating']]]
            );
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('General', ['class' => 'col-md-6'])
            ->add(
                'username',
                null,
                ['row_attr' => ['class' => 'form-floating mb-3']]
            )
            ->add(
                'email',
                EmailType::class,
                ['row_attr' => ['class' => 'form-floating mb-3']]
            );

        $form->add(
            'plainPassword',
            TextType::class,
            [
                'required' => (!$this->getSubject()
                    || is_null(
                        $this->getSubject()->getId()
                    )),
                'row_attr' => ['class' => 'form-floating mb-3'],
            ]
        );

        $form->end()
            ->with('Profile', ['class' => 'col-md-6'])
            ->add(
                'firstname',
                null,
                [
                    'required' => false,
                    'row_attr' => ['class' => 'form-floating mb-3'],
                ]
            )
            ->add(
                'lastname',
                null,
                [
                    'required' => false,
                    'row_attr' => ['class' => 'form-floating mb-3'],
                ]
            )
            ->add(
                'locale',
                ChoiceType::class,
                [
                    'choices' => $this->getLocaleChoices(),
                    'choice_translation_domain' => false,
                    'row_attr' => ['class' => 'form-floating mb-3'],
                ]
            )
            ->end();
        if ($this->googleAuthEnabled) {
            $form->with('Keys', ['label' => false])
                ->add(
                    'twoStepVerificationCode',
                    TextType::class,
                    [
                        'required' => false,
                        'attr' => ['group_class' => 'form-floating', 'readonly' => true],
                        'block_prefix' => 'input_group',
                        'widget_btn_append' => [
                            'icon' => 'copy fs-2',
                            'class' => 'btn-light-primary copy-to-clipboard btn-outline',
                            'attr' => [
                                'data-clipboard-target' => '{{ id }}',
                            ],
                        ]

                    ]
                )
                ->end();
        }
        $form->with('Groups', ['class' => 'col-md-6'])
            ->add('groups', ModelType::class, [
                'class' => Group::class,
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ])
            ->end();

        if (!$this->getSubject()->hasRole('ROLE_SUPER_ADMIN')) {
            $form
                ->with('Management', ['class' => 'col-md-12'])
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
                        ],
                    ]
                )
                ->add(
                    'enabled',
                    CheckboxType::class,
                    [
                        'label_render' => true,
                        'widget_checkbox_label' => 'widget',
                        'label_attr' => ['class' => 'checkbox-line'],
                    ]
                )
                ->end();
        }
    }


    /**
     * Provide an array of locales where the locale is the key and the label is
     * the value for easy display in a dropdown select for example
     * example: array('de_CH' => 'Deutsch', 'en_GB' => 'English').
     */
    public  function getLocaleChoices(): array
    {
        $localeChoices = [];

        if (!$this->getRequest()) {
            return [];
        }
        $locale = $this->getRequest()->getLocale();

        $localeList = Locales::getNames(substr($locale, 0, 2));

        foreach ($this->languages as $language) {
            $localeChoices[$localeList[$language['locale']]]
                = $language['locale'];
        }

        return $localeChoices;
    }

    /**
     * Set the language paramenter to contain a list of languages most likely
     * passed from the config.yaml file.
     */
    public function setLanguages(array $languages): void
    {
        $this->languages = $languages;
    }
}
