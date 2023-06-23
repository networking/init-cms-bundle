<?php

declare(strict_types=1);

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
 * Class EnumPageVisibilityType.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class EnumPageVisibilityType extends EnumType
{
    /**
     * @var string
     */
    protected $name = 'enumpagevisibility';
    /**
     * @var array
     */
    protected $values = ['public', 'protected'];
}
