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
    const PERMISSION_VIEW        = 'VIEW';
    const PERMISSION_EDIT        = 'EDIT';
    const PERMISSION_CREATE      = 'CREATE';
    const PERMISSION_DELETE      = 'DELETE';
    const PERMISSION_UNDELETE    = 'UNDELETE';
    const PERMISSION_LIST        = 'LIST';
    const PERMISSION_EXPORT      = 'EXPORT';
    const PERMISSION_OPERATOR    = 'OPERATOR';
    const PERMISSION_MASTER      = 'MASTER';
    const PERMISSION_OWNER       = 'OWNER';
    const PERMISSION_PUBLISH     = 'PUBLISH';

    /**
     * Map each permission to the permissions it should grant access for
     * fe. grant access for the view permission if the user has the edit permission
     *
     * @var array
     */
    private $map = array(

        self::PERMISSION_VIEW => array(
            MaskBuilder::MASK_VIEW,
            MaskBuilder::MASK_LIST,
            MaskBuilder::MASK_EDIT,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER
        ),

        self::PERMISSION_EDIT => array(
            MaskBuilder::MASK_EDIT,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER
        ),

        self::PERMISSION_CREATE => array(
            MaskBuilder::MASK_CREATE,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER
        ),

        self::PERMISSION_DELETE => array(
            MaskBuilder::MASK_DELETE,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER
        ),

        self::PERMISSION_UNDELETE => array(
            MaskBuilder::MASK_UNDELETE,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER
        ),

        self::PERMISSION_LIST => array(
            MaskBuilder::MASK_LIST,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER
        ),

        self::PERMISSION_EXPORT => array(
            MaskBuilder::MASK_EXPORT,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER
        ),

        self::PERMISSION_OPERATOR => array(
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER
        ),

        self::PERMISSION_MASTER => array(
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        ),

        self::PERMISSION_OWNER => array(
            MaskBuilder::MASK_OWNER,
        ),

        self::PERMISSION_PUBLISH => array(
            MaskBuilder::MASK_PUBLISH,
        ),
    );

    /**
     * {@inheritDoc}
     */
    public function getMasks($permission, $object)
    {
        if (!isset($this->map[$permission])) {

            return null;
        }

        return $this->map[$permission];
    }

    /**
     * {@inheritDoc}
     */
    public function contains($permission)
    {
        return isset($this->map[$permission]);
    }
}