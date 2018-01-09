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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

/**
 * Class IconradioType
 * @package Networking\InitCmsBundle\Form\Type
 * @author Sonja Brodersen <s.brodersen@networking.ch>
 */
class IconradioType extends AbstractType
{
    /**
     * @var array
     */
    private $templates;

    /**
     * @param $templates
     */
    public function __construct(array $templates)
    {
        $this->templates = $templates;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            array(
                'icons' => $this->getIconsFromTemplates()
            )
        );
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars = array_replace(
            $view->vars,
            array(
                'icons' => $options['icons'],
            )
        );
    }

    /**
     * @return array
     */
    private function getIconsFromTemplates()
    {
        $choices = array();
        foreach ($this->templates as $key => $template) {
            $choices[$key] = isset($template['icon']) ? $template['icon'] : '';
        }

        return $choices;
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'networking_type_iconradio';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'networking_type_iconradio';
    }
}