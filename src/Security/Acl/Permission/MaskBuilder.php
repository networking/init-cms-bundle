<?php
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
    public const MASK_LIST = 4096;       // 1 << 12
    public const MASK_EXPORT = 8192;       // 1 << 13


    /**
     * @const string
     */
    const MASK_PUBLISH = 16384;       // 1 << 14

    /**
     * @const string
     */
    const CODE_PUBLISH = 'P';
    public const CODE_LIST = 'L';
    public const CODE_EXPORT = 'E';
}
