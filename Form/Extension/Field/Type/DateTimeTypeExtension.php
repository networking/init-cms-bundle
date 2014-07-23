<?php
namespace Networking\InitCmsBundle\Form\Extension\Field\Type;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class DateTimeTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     *
     * bypass the IntlDateFormatter default pattern, which comes always
     * delivered as $options['formatter'] and
     *     $form->getConfig()->getAttribute('formatter')->getPattern();
     * – use own pattern instead without changing the default date format
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ('single_text' === $options['date_widget'] && isset($options['datepicker'])) {
            $view->children['date']->vars['datepicker'] = $options['datepicker'];
            $view->children['date']->vars['format'] = $options['date_format'];
            $view->children['time']->vars['timepicker'] = $options['timepicker'];
            $view->children['date']->vars['widget_addon'] = array('type' => 'append', 'icon' => 'calendar');
        }

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(
            array(
                'datepicker',
                'timepicker'
            )
        );
    }

    public function getExtendedType()
    {
        return 'datetime';
    }
}
