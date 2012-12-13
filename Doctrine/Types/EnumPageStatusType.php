<?php

namespace Networking\InitCmsBundle\Doctrine\Types;

use Networking\InitCmsBundle\Doctrine\Types\EnumType;

class EnumPageStatusType extends EnumType
{
    protected $name = 'enumpagestatus';
    protected $values = array('draft', 'review', 'published');
}