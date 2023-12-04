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

namespace Networking\InitCmsBundle\Admin\Extension;

use Networking\InitCmsBundle\Filter\SimpleStringFilter;
use Networking\InitCmsBundle\Form\Type\GalleryImageType;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class GalleryAdmin.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class GalleryAdminExtension extends AbstractAdminExtension
{
    public function __construct(
        private Pool $pool
    ) {
    }

    public function configureFormFields(FormMapper $form): void
    {
        $form
            ->remove('galleryItems')
            ->remove('context')
            ->remove('name')
            ->remove('enabled')
            ->remove('defaultFormat')
        ;

        // define group zoning
        $form
            ->with('Gallery', ['class' => 'col-md-9'])->end()
            ->with('Options', ['class' => 'col-md-3'])->end();

        $context = $form->getAdmin()->getPersistentParameter('context',$this->pool->getDefaultContext());

        if (!$context) {
            $context = $this->pool->getDefaultContext();
        }

        $formats = [];
        foreach ((array) $this->pool->getFormatNamesByContext($context) as $name => $options) {
            $formats[$name] = $name;
        }

        $contexts = [];
        foreach ((array) $this->pool->getContexts() as $contextItem => $format) {
            $contexts[$contextItem] = $contextItem;
        }

        $form
            ->with('Options')
//            ->add('context', ChoiceType::class, [
//                'choices' => $contexts,
//                'choice_translation_domain' => 'SonataMediaBundle',
//            ])
            ->add('name')
            ->add('enabled', null, ['required' => false])
            ->ifTrue(count($formats) > 0)
            ->add('defaultFormat', ChoiceType::class, ['choices' => $formats])
            ->ifEnd()
            ->end()
            ->with('Gallery')
            ->add(
                'galleryItems',
                CollectionType::class,
                [
                    'block_prefix' => 'gallery',
                    'sortable' => 'position',
                    'by_reference' => false,
                    'entry_type' => GalleryImageType::class,
                    'entry_options' => [
                        'label' => false,
                        'label_render' => false,
                        'link_parameters' => ['context' => $context],
                        'selected' => [],
                    ],
                    'widget_add_btn' => [
                        'label' => false,
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'required' => false,
                    'prototype' => true,
                    'label' => false,
                    'translation_domain' => 'admin',
                ]
            )
            ->end();
    }

    public function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->remove('context');
        $filter->add(
            'context',
            SimpleStringFilter::class,
            [
                'show_filter' => false,
                'operator_type' => ContainsOperatorType::TYPE_EQUAL,
                'field_type' => HiddenType::class,
                'force_case_insensitivity' => true,
            ]
        );
    }

    public function configureListFields(ListMapper $list): void
    {
        $list->remove('defaultFormat')
            ->remove('context')
            ->add(
                '_action',
                'actions',
                [
                    'actions' => [
                        'edit' => [],
                        'delete' => [],
                    ],
                ]
            );
    }
}
