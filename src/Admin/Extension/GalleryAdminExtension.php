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
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class GalleryAdmin.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class GalleryAdminExtension extends AbstractAdminExtension
{
    /**
     * {@inheritdoc}
     */
    public function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper->remove('context')
            ->add('context', HiddenType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper->remove('context');
        $datagridMapper->add(
            'context',
            SimpleStringFilter::class,
            [
                'show_filter' => false,
                'operator_type' => ContainsOperatorType::TYPE_EQUAL,
                'field_type' => HiddenType::class,
                'case_sensitive' => true
            ]
        )
            ->add(
                'providerName',
                SimpleStringFilter::class,
                [
                    'operator_type' => ContainsOperatorType::TYPE_EQUAL,
                    'case_sensitive' => true
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->remove('defaultFormat')
            ->remove('context')
            ->add(
                '_action',
                'actions',
                [
                    'actions' => [
                        'show' => [],
                        'edit' => [],
                        'delete' => [],
                    ],
                ]
            );
    }
}
