<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Admin\Model;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\Admin\GalleryHasMediaAdmin as Admin;

class GalleryHasMediaAdmin extends Admin
{
    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $context = 'default';

        if ($this->hasParentFieldDescription()) {
            $link_parameters = $this->getParentFieldDescription()->getOption('link_parameters', array());
            $context = array_key_exists('context', $link_parameters)?$link_parameters['context']:$context;
        }


        if ($this->hasRequest()) {
            $context = $this->getRequest()->get('context', $context);
        }

        $formMapper
            ->add(
                'media',
                'media_entity_type',
                array('required' => false, 'context' => $context))
            ->add('enabled', null, array('required' => false))
            ->add('position', 'hidden')
        ;
    }
}
