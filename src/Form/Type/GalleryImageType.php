<?php

namespace Networking\InitCmsBundle\Form\Type;

use Networking\InitCmsBundle\Entity\GalleryItem;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class GalleryImageType extends \Symfony\Component\Form\AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'media',
                MediaEntityType::class,
                [
                    'context' => $options['link_parameters']['context'],
                    'label' => false,
                    'constraints' => new NotNull(),
                    'widget_form_group_attr' => ['class' => 'form-group col-sm-10'],
                ]
            )
            ->add('position', HiddenType::class, ['attr' => ['class' => 'position']]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $data = $form->getData();
        $selected = false;

        if ($data instanceof GalleryItem && $data->getId()) {
            $selected = $data->getMedia()->getId();
        }

        $view->vars['selected'] = $selected;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => GalleryItem::class,
                'translation_domain' => 'admin',
                'widget_remove_btn' => [
                    'label' => false,
                    'wrapper_div' => false,
                ],
                'link_parameters' => ['context' => 'default'],
                'selected' => [],
            ]
        );
    }
}
