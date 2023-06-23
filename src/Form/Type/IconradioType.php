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
namespace Networking\InitCmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

/**
 * Class IconradioType.
 *
 * @author Sonja Brodersen <s.brodersen@networking.ch>
 */
class IconradioType extends AbstractType
{
    /**
     * @param $templates
     */
    public function __construct(private readonly array $templates)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'icons' => $this->getIconsFromTemplates(),
            ]
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars = array_replace(
            $view->vars,
            [
                'icons' => $options['icons'],
            ]
        );
    }

    private function getIconsFromTemplates(): array
    {
        $choices = [];
        foreach ($this->templates as $key => $template) {
            $choices[$key] = $template['icon'] ?? '';
        }

        return $choices;
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'networking_type_iconradio';
    }
}
