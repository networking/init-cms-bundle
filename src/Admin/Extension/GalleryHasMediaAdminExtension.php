<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Admin\Extension;

use Networking\InitCmsBundle\Form\Type\MediaEntityType;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\MediaBundle\Admin\GalleryHasMediaAdmin as Admin;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class GalleryHasMediaAdminExtension extends AbstractAdminExtension
{
    public function configureFormFields(FormMapper $formMapper)
    {
    }
}
