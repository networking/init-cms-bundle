<?php
/**
 * Created by PhpStorm.
 * User: yorkie
 * Date: 04.12.17
 * Time: 13:34.
 */

namespace Networking\InitCmsBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Select2Extension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (false === $options['expanded'] && isset($options['select2'])) {
            $view->vars['select2'] = $options['select2'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('select2');
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return ChoiceType::class;
    }

    /**
     * @return array|iterable
     */
    public static function getExtendedTypes(): iterable
    {
        return [ChoiceType::class];
    }
}
