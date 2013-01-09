<?php


namespace Networking\InitCmsBundle\Security\Acl\Permission;

use Sonata\AdminBundle\Security\Acl\Permission\MaskBuilder as SonataBaseMaskBuilder;

/**
 * {@inheritDoc}
 * - LIST: the SID is allowed to view a list of the domain objects / fields
 */
class MaskBuilder extends SonataBaseMaskBuilder
{
    const MASK_PUBLISH       = 16384;       // 1 << 14

    const CODE_PUBLISH      = 'P';
}