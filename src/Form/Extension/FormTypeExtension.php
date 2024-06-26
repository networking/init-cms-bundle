<?php

namespace Networking\InitCmsBundle\Form\Extension;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormTypeExtension extends \Symfony\Component\Form\AbstractTypeExtension
{

    /**
     * @var array
     */
    protected $options;


    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {

        $layout = $options['layout'];

        if ($layout === null) {
            if ($view->parent) {
                $layout = $view->parent->vars['layout'];
            } else {
                $layout = null;
            }
        }



        $view->vars = \array_replace($view->vars, [
            'layout' => $layout,
            'horizontal_label_class' => $options['horizontal_label_class'],
            'horizontal_label_offset_class' => $options['horizontal_label_offset_class'],
            'horizontal_input_wrapper_class' => $options['horizontal_input_wrapper_class'],
            'horizontal_label_div_class' => $options['horizontal_label_div_class'],
        ]);

        if (\in_array('percent', $view->vars['block_prefixes'], true) && null === $options['widget_addon_append']) {
            $options['widget_addon_append'] = [];
        }

        if (\in_array('money', $view->vars['block_prefixes'], true) && null === $options['widget_addon_prepend']) {
            $options['widget_addon_prepend'] = [];
        }
        $view->vars['widget_form_control_class'] = $options['widget_form_control_class'];
        $view->vars['label_render'] = $options['label_render'];
        $view->vars['widget_form_group'] = $options['widget_form_group'];
        $view->vars['widget_addon_prepend'] = $options['widget_addon_prepend'];
        $view->vars['widget_addon_append'] = $options['widget_addon_append'];
        $view->vars['widget_btn_prepend'] = $options['widget_btn_prepend'];
        $view->vars['widget_btn_append'] = $options['widget_btn_append'];
        $view->vars['widget_prefix'] = $options['widget_prefix'];
        $view->vars['widget_suffix'] = $options['widget_suffix'];
        $view->vars['widget_type'] = $options['widget_type'];
        $view->vars['widget_items_attr'] = $options['widget_items_attr'];
        $view->vars['widget_form_group_attr'] = $options['widget_form_group_attr'];
        $view->vars['widget_checkbox_label'] = $options['widget_checkbox_label'];
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if (!$view->parent && $options['compound'] && $view->vars['layout']) {
            $class = isset($view->vars['attr']['class']) ? $view->vars['attr']['class'].' ' : '';
            $view->vars['attr']['class'] = $class.'form-'.$view->vars['layout'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'layout' => null,
            'horizontal' => false,
            'label_render' => true,
            'horizontal_label_class' => null,
            'horizontal_label_offset_class' => null,
            'horizontal_input_wrapper_class' => null,
            'horizontal_label_div_class' => null,
            'widget_form_control_class' => 'form-control',
            'widget_form_group' => true,
            'widget_addon_prepend' => null,
            'widget_addon_append' => null,
            'widget_btn_prepend' => null,
            'widget_btn_append' => null,
            'widget_prefix' => null,
            'widget_suffix' => null,
            'widget_type' => '',
            'widget_items_attr' => [],
            'widget_form_group_attr' => [
                'class' => 'form-group',
            ],
            'widget_checkbox_label' => 'label',
        ]);


        $resolver->setAllowedValues('layout', [false, null, 'horizontal', 'inline']);
        $resolver->setAllowedValues('widget_type', ['inline', 'inline-btn', '']);
        $resolver->setAllowedValues('widget_checkbox_label', ['label', 'widget', 'both']);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [
            FormType::class,
        ];
    }
}