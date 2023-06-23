<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Networking\InitCmsBundle\Admin\Model;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Networking\InitCmsBundle\Admin\BaseAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class HelpTextAdmin.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class HelpTextAdmin extends BaseAdmin
{

    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'cms/help';
    }


    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('title')
            ->addIdentifier('translationKey')
            ->add('locale')
            ->add(
                '_action',
                'actions',
                [
                    'label' => false,
                    'actions' => [
                        'edit' => [],
                        'delete' => [],
                    ],
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->with('', ['label' => false])
            ->add(
                'locale',
                ChoiceType::class,
                [
                    'choice_loader' => new CallbackChoiceLoader(fn() => $this->getLocaleChoices()),
                    'preferred_choices' => [$this->getDefaultLocale()],
                    'translation_domain' => false,
                ]
            )
            ->add('translationKey')
            ->add('title', null, ['required' => true])
            ->add(
                'text',
                CKEditorType::class,
                ['config' => ['toolbar' => 'standard', 'contentsCss' => null]]
            )
        ->end();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add(
                'locale',
                CallbackFilter::class,
                [
                    'callback' => $this->getByLocale(...),
                    'hidden' => false,
                ],
                [
                    'field_type' => ChoiceType::class,
                    'field_options' => [
                        'placeholder' => false,
                        'choice_loader' => new CallbackChoiceLoader(fn() => $this->getLocaleChoices()),
                        'preferred_choices' => [$this->getDefaultLocale()],
                        'translation_domain' => false,
                    ]
                ]

            );
    }

    /**
     * @param array $filterValues
     */
    public function configureDefaultFilterValues(array &$filterValues): void
    {
        $filterValues['locale'] = [
            'type' => ContainsOperatorType::TYPE_EQUAL,
            'value' => $this->getDefaultLocale(),
        ];
    }

    /**
     * @param $alias
     * @param $field
     * @param $data
     * @return bool
     */
    public function getByLocale(ProxyQuery $ProxyQuery, $alias, $field, FilterData $data)
    {
        $locale = $this->getDefaultLocale();
        $active = false;
        if ($data->hasValue()) {
            $locale = $data->getValue();
            $active = true;
        }

        $qb = $ProxyQuery->getQueryBuilder();

        $qb->andWhere(sprintf('%s.%s = :locale', $alias, $field));
        $qb->orderBy(sprintf('%s.translationKey', $alias), 'asc');
        $qb->setParameter(':locale', $locale);

        return $active;
    }
}
