<?php
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
 * Class PageBatchTranslationType
 * @package Networking\InitCmsBundle\Form\Type
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageBatchCopyType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];
        foreach ($options['locales'] as $value){

            $choices[$value['locale']] = $value['locale'];
        }

        $builder
            ->add('fromLocale', 'choice', ['label' => 'form.label_from_locale', 'choices' => $choices])
            ->add('toLocale', 'choice', ['label' => 'form.label_to_locale', 'choices' => $choices]);
    }

    /**
     * @param $data
     * @param ExecutionContextInterface $executionContext
     */
    public function validateChoices($data, ExecutionContextInterface $executionContext){

        if($data['fromLocale'] === $data['toLocale']){
            $executionContext->addViolation('init_cms.page_copy.duplicate_locale');
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined(['locales'])
            ->isRequired('locales')
        ;
        $resolver->setDefault('constraints', new Callback([$this, 'validateChoices']));
        $resolver->setDefault('translation_domain', 'PageAdmin');
    }

}