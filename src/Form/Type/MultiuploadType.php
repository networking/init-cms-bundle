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
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MultiuploadType.
 *
 * @author Marc Bissegger <m.bissegger@networking.ch>
 */
class MultiuploadType extends AbstractType
{
    public function getParent(): string
    {
        return 'file';
    }

    public function getName(): string
    {
        return 'networking_type_multiupload';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'error_mapping' => [
                'validate' => 'binaryContent',
            ],
        ]);
    }
}
