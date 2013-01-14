<?php

/*
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Doctrine\Types;

use Networking\InitCmsBundle\Doctrine\Types\EnumType;

/**
 * @author net working AG <info@networking.ch>
 */
class EnumPageVisibilityType extends EnumType
{
    /**
     * @var string $name
     */
    protected $name = 'enumpagevisibility';
    /**
     * @var array $values
     */
    protected $values = array('public', 'protected');
}