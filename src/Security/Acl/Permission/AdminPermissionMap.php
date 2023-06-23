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

use Symfony\Component\Security\Acl\Permission\PermissionMapInterface;

/**
 * This is basic permission map complements the masks which have been defined
 * on the standard implementation of the MaskBuilder.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Yorkie Chadwick <y.chadwick@networkgin.ch>
 */
class AdminPermissionMap implements PermissionMapInterface
{
    final public const PERMISSION_VIEW = 'VIEW';
    final public const PERMISSION_EDIT = 'EDIT';
    final public const PERMISSION_CREATE = 'CREATE';
    final public const PERMISSION_DELETE = 'DELETE';
    final public const PERMISSION_UNDELETE = 'UNDELETE';
    final public const PERMISSION_LIST = 'LIST';
    final public const PERMISSION_EXPORT = 'EXPORT';
    final public const PERMISSION_OPERATOR = 'OPERATOR';
    final public const PERMISSION_MASTER = 'MASTER';
    final public const PERMISSION_OWNER = 'OWNER';
    final public const PERMISSION_PUBLISH = 'PUBLISH';

    /**
     * Map each permission to the permissions it should grant access for
     * fe. grant access for the view permission if the user has the edit permission.
     */
    private array $map = [

        self::PERMISSION_VIEW => [
            MaskBuilder::MASK_VIEW,
            MaskBuilder::MASK_LIST,
            MaskBuilder::MASK_EDIT,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        ],

        self::PERMISSION_EDIT => [
            MaskBuilder::MASK_EDIT,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        ],

        self::PERMISSION_CREATE => [
            MaskBuilder::MASK_CREATE,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        ],

        self::PERMISSION_DELETE => [
            MaskBuilder::MASK_DELETE,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        ],

        self::PERMISSION_UNDELETE => [
            MaskBuilder::MASK_UNDELETE,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        ],

        self::PERMISSION_LIST => [
            MaskBuilder::MASK_LIST,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        ],

        self::PERMISSION_EXPORT => [
            MaskBuilder::MASK_EXPORT,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        ],

        self::PERMISSION_OPERATOR => [
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        ],

        self::PERMISSION_MASTER => [
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        ],

        self::PERMISSION_OWNER => [
            MaskBuilder::MASK_OWNER,
        ],

        self::PERMISSION_PUBLISH => [
            MaskBuilder::MASK_PUBLISH,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getMasks($permission, $object): ?array
    {
        if (!isset($this->map[$permission])) {
            return null;
        }

        return $this->map[$permission];
    }

    /**
     * {@inheritdoc}
     */
    public function contains($permission): bool
    {
        return isset($this->map[$permission]);
    }
}
