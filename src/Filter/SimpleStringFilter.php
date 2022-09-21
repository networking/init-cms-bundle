<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Filter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use Sonata\DoctrineORMAdminBundle\Filter\Filter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class SimpleStringFilter.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class SimpleStringFilter extends Filter
{

    public const CHOICES = [
        ContainsOperatorType::TYPE_CONTAINS => 'LIKE',
        ContainsOperatorType::TYPE_NOT_CONTAINS => 'NOT LIKE',
        ContainsOperatorType::TYPE_EQUAL => '=',
    ];
    /**
     * {@inheritdoc}
     */
    public function filter(
        \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQueryInterface $query, string $alias, string $field, FilterData $data): void
    {



        if (!$data->hasValue()) {
            return;
        }

        $operator = $this->getOperator((int) $this->getOption('operator_type'));

        if (!$operator) {
            $operator = 'LIKE';
        }


        $parameterName = $this->getNewParameterName($query);

        $queryBuilder = $query->getQueryBuilder();
        $or = $queryBuilder->expr()->orX();

        if ($this->getOption('case_sensitive')) {
            $or->add(sprintf('%s.%s %s :%s', $alias, $field, $operator, $parameterName));
        } else {
            $or->add(sprintf('LOWER(%s.%s) %s :%s', $alias, $field, $operator, $parameterName));
        }

        if (ContainsOperatorType::TYPE_NOT_CONTAINS === $operator) {
            $or->add($queryBuilder->expr()->isNull(sprintf('%s.%s', $alias, $field)));
        }

        $this->applyWhere($query, $or);


        if (ContainsOperatorType::TYPE_EQUAL === $this->getOption('operator_type')) {
            $queryBuilder->setParameter($parameterName, $data->getValue());
        } else {
            $queryBuilder->setParameter($parameterName,
                sprintf(
                    $this->getOption('format'),
                    $this->getOption('case_sensitive') ? $data->getValue() : mb_strtolower($data->getValue())
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return [
            'format' => '%%%s%%',
            'field_type' => TextType::class,
            'operator_type' => ContainsOperatorType::TYPE_CONTAINS,
            'label_render' => true,
            'widget_form_group' => true,
            'case_sensitive' => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getLabelRender()
    {
        return $this->getOption('label_render');
    }

    public function getWidgetControlGroup()
    {
        if ($this->getFieldType() == 'hidden') {
            return false;
        }

        return $this->getOption('widget_form_group');
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings(): array
    {
        return [
            DefaultType::class,
            [
                'field_type' => $this->getFieldType(),
                'field_options' => $this->getFieldOptions(),
                'label' => $this->getLabel(),
                'label_render' => $this->getLabelRender(),
                'widget_form_group' => $this->getWidgetControlGroup(),
            ],
        ];
    }


    /**
     * @param string $type
     *
     * @return bool
     */
    private function getOperator($type)
    {
        return self::CHOICES[$type] ?? false;
    }
}
