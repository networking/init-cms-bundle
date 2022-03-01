<?php

namespace Networking\InitCmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QrCodeType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['qrCodeUrl'] = $options['qrCodeUrl'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['qrCodeUrl']);
    }
}