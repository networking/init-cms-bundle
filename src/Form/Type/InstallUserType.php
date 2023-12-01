<?php

declare(strict_types=1);

/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class InstallUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
            'email',
            EmailType::class,
            [
                'required' => true,
                'constraints' => [new Email(), new NotBlank()],
            ]
        )
            ->add(
            'password',
            RepeatedType::class,
            [
                'required' => true,
                'type' => PasswordType::class,
                'constraints' => [new NotBlank()],
                'options' => ['translation_domain' => 'SonataUserBundle'],
                'first_options' => ['label' => 'form.password'],
                'second_options' => ['label' => 'form.password_confirmation'],
                'invalid_message' => 'password.mismatch',
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'user';
    }
}
