<?php
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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class InstallUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'username',
            'text',
            array(
                'required' => true,
                'constraints' => array(new NotBlank())
            )
        )
            ->add(
            'email',
            'email',
            array(
                'required' => true,
                'constraints' => array(new Email())
            )
        )
            ->add(
            'password',
            'repeated',
            array(
                'required' => true,
                'type' => 'password',
                'constraints' => array(new NotBlank()),
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.password'),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
            )
        );
    }

    public function getName()
    {
        return 'user';
    }
}