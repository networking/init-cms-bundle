<?php

declare(strict_types=1);

/**
 * This file is part of the sko  package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Form\Extension;

use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TagExtension.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class ModelTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if (true === $options['multiple'] && false === $options['expanded'] && isset($options['taggable'])) {
            $view->vars['taggable'] = $options['taggable'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['transformer']) {
            $builder->addViewTransformer($options['transformer'], true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['taggable' => false, 'transformer' => false]);
        $resolver->setDefined(['taggable', 'transformer']);
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType(): string
    {
        return ModelType::class;
    }

    /**
     * @return array|iterable
     */
    public static function getExtendedTypes(): iterable
    {
        return [ModelType::class];
    }
}
