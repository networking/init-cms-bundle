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
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\DoctrineORMAdminBundle\Filter\Filter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class SimpleStringFilter
 * @package Networking\InitCmsBundle\Filter
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class SimpleStringFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
            return;
        }

        $data['value'] = trim($data['value']);

        if (strlen($data['value']) == 0) {
            return;
        }

        $operator = 'LIKE';

        // c.name > '1' => c.name OPERATOR :FIELDNAME
        $parameterName = $this->getNewParameterName($queryBuilder);
        $this->applyWhere($queryBuilder, sprintf('%s.%s %s :%s', $alias, $field, $operator, $parameterName));
        $queryBuilder->setParameter($parameterName, sprintf($this->getOption('format'), $data['value']));
    }


    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return [
            'format' => '%%%s%%',
            'field_type' => TextType::class,
            'label_render' => true,
            'widget_form_group' => true
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
        if($this->getFieldType() == 'hidden'){
            return false;
        }
        return $this->getOption('widget_form_group');
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return [
            DefaultType::class,
            [
                'field_type' => $this->getFieldType(),
                'field_options' => $this->getFieldOptions(),
                'label' => $this->getLabel(),
                'label_render' => $this->getLabelRender(),
                'widget_form_group' => $this->getWidgetControlGroup()
            ]
        ];
    }
}
