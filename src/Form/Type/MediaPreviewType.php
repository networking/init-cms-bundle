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

use Networking\InitCmsBundle\Entity\Media;
use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MediaPrintype.
 *
 * @author Marc Bissegger <m.bissegger@networking.ch>
 */
class MediaPreviewType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $media = $form->getData();
        if (!$media instanceof MediaInterface) {
            throw new InvalidArgumentException('This field can only be used with objects that are instances of Sonata\MediaBundle\Model\MediaInterface');
        }

        if ($form->getParent()->getErrors(true)->count() > 0) {
            /** @var Media $media */
            $media = $view->vars['value'];

            $media->setProviderReference($media->getPreviousProviderReference());
            $view->vars['value'] = $media;
        }

        $contentType = $media->getContentType();

        $view->vars = array_replace(
            $view->vars,
            [
                'is_image' => str_starts_with($contentType, 'image'),
                'provider' => $options['provider'],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('is_image', true);

        $resolver->setRequired(['provider']);
    }

    public function getBlockPrefix(): string
    {
        return 'networking_type_media_preview';
    }
}
