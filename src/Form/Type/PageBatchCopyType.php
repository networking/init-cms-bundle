<?php

declare(strict_types=1);

/**
 * This file is part of the schuler-shop  package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class PageBatchTranslationType.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageBatchCopyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [];
        foreach ($options['locales'] as $value) {
            $choices[$value['locale']] = $value['locale'];
        }

        $builder
            ->add('fromLocale', 'choice', ['label' => 'form.label_from_locale', 'choices' => $choices])
            ->add('toLocale', 'choice', ['label' => 'form.label_to_locale', 'choices' => $choices]);
    }

    /**
     * @param $data
     */
    public function validateChoices($data, ExecutionContextInterface $executionContext): void
    {
        if ($data['fromLocale'] === $data['toLocale']) {
            $executionContext->addViolation('init_cms.page_copy.duplicate_locale');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['locales'])
            ->isRequired('locales')
        ;
        $resolver->setDefault('constraints', new Callback($this->validateChoices(...)));
        $resolver->setDefault('translation_domain', 'PageAdmin');
    }
}
