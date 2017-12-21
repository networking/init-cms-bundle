<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class GalleryAdmin
 * @package Networking\InitCmsBundle\Admin\Model
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class GalleryAdminExtension extends AbstractAdminExtension
{

    /**
     * {@inheritdoc}
     */
    public function configureFormFields(FormMapper $formMapper)
    {
       $formMapper->remove('context')->add('context', HiddenType::class);
    }


}
