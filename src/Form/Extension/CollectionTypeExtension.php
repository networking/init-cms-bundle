<?php

namespace Networking\InitCmsBundle\Form\Extension;

use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollectionTypeExtension
    extends \Symfony\Component\Form\AbstractTypeExtension
{

    const DEFAULTS = [
        'widget_add_btn' => ['label' => 'add_item', 'attr' => ['class' => 'btn btn-sm btn-light-primary add-button'], 'icon' => 'fa fa-plus'],
        'widget_remove_btn' => [
            'label' => 'remove_item',
            'wrapper_div' => ['class' => 'form-group'],
            'horizontal_wrapper_div' => ['class' => 'col-sm-3 col-sm-offset-3'],
            'attr' => ['class' => 'btn btn-sm btn-light-danger remove-button'],
            'icon' => 'fa fa-trash'
        ]
    ];

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {

        if (\in_array('collection', $view->vars['block_prefixes'], true)) {
            if ($options['widget_add_btn'] !== null && !\is_array($options['widget_add_btn'])) {
                throw new InvalidArgumentException('The "widget_add_btn" option must be an "array".');
            }

            if ((isset($options['allow_add']) && true === $options['allow_add']) && $options['widget_add_btn']) {
                if (isset($options['widget_add_btn']['attr']) && !\is_array($options['widget_add_btn']['attr'])) {
                    throw new InvalidArgumentException('The "widget_add_btn.attr" option must be an "array".');
                }
                $options['widget_add_btn'] = \array_replace_recursive(self::DEFAULTS['widget_add_btn'], $options['widget_add_btn']);
            }
        }

        if ($view->parent && \in_array('collection', $view->parent->vars['block_prefixes'], true)) {
            if ($options['widget_remove_btn'] !== null && !\is_array($options['widget_remove_btn'])) {
                throw new InvalidArgumentException('The "widget_remove_btn" option must be an "array".');
            }

            if ((isset($view->parent->vars['allow_delete']) && true === $view->parent->vars['allow_delete']) && $options['widget_remove_btn']) {
                if (isset($options['widget_remove_btn']) && !\is_array($options['widget_remove_btn'])) {
                    throw new InvalidArgumentException('The "widget_remove_btn" option must be an "array".');
                }
                $options['widget_remove_btn'] = \array_replace_recursive(self::DEFAULTS['widget_remove_btn'], $options['widget_remove_btn']);
            }
        }

        $view->vars['omit_collection_item'] = $options['omit_collection_item'];
        $view->vars['widget_add_btn'] = $options['widget_add_btn'];
        $view->vars['widget_remove_btn'] = $options['widget_remove_btn'];
        $view->vars['prototype_names'] = $options['prototype_names'];
        $view->vars['show_legend'] = $options['show_legend'];
    }
        /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {

        $resolver->setDefaults([
            'omit_collection_item' => true,
            'widget_add_btn' => self::DEFAULTS['widget_add_btn'],
            'widget_remove_btn' => self::DEFAULTS['widget_remove_btn'],
            'show_legend' => false,
            'prototype_names' => [],
        ]);


    }

    /**
     * @inheritDoc
     */
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class,];
    }
}