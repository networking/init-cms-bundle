<?php

namespace Networking\InitCmsBundle\Doctrine\Types;

use Networking\InitCmsBundle\Doctrine\Types\EnumType;

class EnumPageVisibilityType extends EnumType
{
    protected $name = 'enumpagevisibility';
    protected $values = array('public', 'protected');
}