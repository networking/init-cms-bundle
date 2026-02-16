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

namespace Networking\InitCmsBundle\Form\Type;

use Networking\InitCmsBundle\Form\DataTransformer\ModelToIdTransformer;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MediaEntityType.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MediaEntityType extends AbstractType
{

    public Pool $pool;

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (null === $options['model_manager']) {
            $pool = $this->pool;
            $adminCode = $options['admin_code'];

            if (null !== $adminCode) {
                $admin = $pool->getAdminByAdminCode($adminCode);
                $options['class'] = $admin->getClass();
            } else {
                $admin = $pool->getAdminByClass($options['class']);
            }

            $options['model_manager'] = $admin->getModelManager();
        }

        $builder
            ->addViewTransformer(new ModelToIdTransformer($options['model_manager'], $options['class']), true);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $pool = $this->pool;

        $adminCode = $options['admin_code'];

        if (null !== $adminCode) {
            $admin = $pool->getAdminByAdminCode($adminCode);
        } else {
            $admin = $pool->getAdminByClass($options['class']);
        }

        if ($options['provider'] && !$options['provider_name']) {
            $options['provider_name'] = $options['provider'];
        }

        if ($options['context_name'] && !$options['context']) {
            $options['context'] = $options['context_name'];
        }

        $view->vars['admin'] = $admin;
        $view->vars['provider_name'] = $options['provider_name'];
        $view->vars['context'] = $options['context'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'model_manager' => null,
                'class' => null,
                'required' => false,
                'provider' => false,
                'provider_name' => false,
                'context' => false,
                'context_name' => false,
                'admin_code' => 'sonata.media.admin.media',
                'error_bubbling' => false,
                'compound' => false,
            ]
        );

        $resolver->setRequired(['class']);
        $resolver->addAllowedValues('required', [false, true]);
    }

    public function getBlockPrefix(): string
    {
        return 'media_entity_type';
    }
}
