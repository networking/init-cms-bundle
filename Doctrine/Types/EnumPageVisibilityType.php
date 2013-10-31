<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Doctrine\Types;

/**
 * Class EnumPageVisibilityType
 * @package Networking\InitCmsBundle\Doctrine\Types
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
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