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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class MultiuploadType
 * @package Networking\InitCmsBundle\Form\Type
 * @author Marc Bissegger <m.bissegger@networking.ch>
 */
class MultiuploadType extends AbstractType
{
    /**
     * @return string
     */
    public function getParent()
    {
        return 'file';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'networking_type_multiupload';
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver){
        $resolver->setDefaults(array(
            'error_mapping' => array(
                'validate' => 'binaryContent',
            ),
        ));

    }
}