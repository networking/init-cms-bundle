<?php

namespace Networking\InitCmsBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminExtensionInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

/**
 * @method void preBatchAction(AdminInterface $admin, string $actionName, ProxyQueryInterface $query, array &$idx, bool $allElements)
 */
class UserProfileExtension extends AbstractAdminExtension implements AdminExtensionInterface
{
    public function configureFormFields(FormMapper $form): void
    {
        $form->removeGroup('Groups');
        $form->removeGroup('Management');
        $form->removeGroup('Keys');
        $form->remove('plainPassword');
        $form
            ->with('General', ['label' => 'General', 'class' => 'col-md-6'])
            ->add('username')
            ->add('email');

        $form->end()
            ->with('Profile', ['class' => 'col-md-6'])
            ->add('firstname', null, ['required' => false])
            ->add('lastname', null, ['required' => false])

            ->add(
                'locale',
                ChoiceType::class,
                [
                    'choices' => $form->getAdmin()->getLocaleChoices(),
                    'choice_translation_domain' => false,
                    'row_attr' => ['class' => 'form-floating mb-3'],
                ]
            )
            ->end();

        $form
            ->with('password', ['label' => 'title_user_edit_password', 'class' => 'col'])
            ->add(
                'plainPassword',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'required' => false,
                    'first_options' => [
                        'label' => 'form.password',
                        'required' => false,
                        'translation_domain' => 'SonataUserBundle',
                    ],
                    'second_options' => [
                        'label' => 'form.password_confirmation',
                        'required' => false,
                        'translation_domain' => 'SonataUserBundle',
                    ],
                ]
            );
    }
}
