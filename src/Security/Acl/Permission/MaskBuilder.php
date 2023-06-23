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
namespace Networking\InitCmsBundle\Security\Acl\Permission;

use Symfony\Component\Security\Acl\Permission\MaskBuilder as BaseMaskBuilder;

/**
 * {@inheritdoc}
 * - LIST: the SID is allowed to view a list of the domain objects / fields.
 *
 * @author net working AG <info@networking.ch>
 */
class MaskBuilder extends BaseMaskBuilder
{
    final public const MASK_LIST = 4096;       // 1 << 12
    final public const MASK_EXPORT = 8192;       // 1 << 13


    /**
     * @const string
     */
    final public const MASK_PUBLISH = 16384;       // 1 << 14

    /**
     * @const string
     */
    final public const CODE_PUBLISH = 'P';
    final public const CODE_LIST = 'L';
    final public const CODE_EXPORT = 'E';
}
