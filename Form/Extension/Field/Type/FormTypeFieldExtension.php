<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Form\Extension\Field\Type;

use Sonata\AdminBundle\Form\Extension\Field\Type\FormTypeFieldExtension as SonataFormTypeFieldExtension;

class FormTypeFieldExtension extends SonataFormTypeFieldExtension
{
    protected $defaultClasses = array();

    /**
     * @param array $defaultClasses
     */
    public function __construct(array $defaultClasses = array())
    {
        $this->defaultClasses = $defaultClasses;
    }

    public function getDefaultClasses(){
        return $this->defaultClasses;
    }

    public function setDefaultClasses(array $defaultClasses)
    {
        $this->defaultClasses = $defaultClasses;
        return $this;
    }
}