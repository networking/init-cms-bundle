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
/**
 * Class MediaPrintype
 * @package Networking\InitCmsBundle\Form\Type
 * @author Marc Bissegger <m.bissegger@networking.ch>
 */
class MediaPrintType extends AbstractType
{
    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'networking_type_mediaprint';
    }

}