<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface,
    Symfony\Component\Form\Extension\Core\Type\ChoiceType,
    Symfony\Component\Form\FormView,
    Symfony\Component\Form\FormInterface;

/**
 * @author Sonja Brodersen <s.brodersen@networking.ch>
 */
class IconRadioType extends ChoiceType
{
    private $templates;

    public function __construct($templates)
    {
        $this->templates = $templates;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'networking_type_iconradio';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(array(
            'icons' => $this->getIconsFromTemplates()
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars = array_replace($view->vars, array(
            'icons' => $options['icons'],
        ));
    }

    private function getIconsFromTemplates()
    {
        $choices = array();
        foreach ($this->templates as $key => $template) {
            $choices[$template['template']] = isset($template['icon'])?$template['icon']:'';
        }
        return $choices;
    }
}