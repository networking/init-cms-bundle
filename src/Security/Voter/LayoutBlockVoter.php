<?php

namespace Networking\InitCmsBundle\Security\Voter;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class LayoutBlockVoter extends \Symfony\Component\Security\Core\Authorization\Voter\Voter
{
    public const CREATE = 'ROLE_NETWORKING_INIT_CMS_ADMIN_LAYOUT_BLOCK_CREATE';
    public const EDIT = 'ROLE_NETWORKING_INIT_CMS_ADMIN_LAYOUT_BLOCK_EDIT';
    public const DELETE = 'ROLE_NETWORKING_INIT_CMS_ADMIN_LAYOUT_BLOCK_DELETE';
    public const ALL = 'ROLE_NETWORKING_INIT_CMS_ADMIN_LAYOUT_BLOCK_ALL';
    public const PAGE_CREATE = 'ROLE_NETWORKING_INIT_CMS_ADMIN_PAGE_CREATE';
    public const PAGE_EDIT = 'ROLE_NETWORKING_INIT_CMS_ADMIN_PAGE_EDIT';
    public const PAGE_DELETE = 'ROLE_NETWORKING_INIT_CMS_ADMIN_PAGE_DELETE';
    public const PAGE_ALL = 'ROLE_NETWORKING_INIT_CMS_ADMIN_PAGE_ALL';

    public function __construct(
        private readonly Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::EDIT, self::DELETE, self::DELETE]);
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
        $role = match ($attribute) {
            self::CREATE => self::PAGE_CREATE,
            self::EDIT => self::PAGE_EDIT,
            self::DELETE => self::PAGE_DELETE,
            self::ALL => self::PAGE_ALL,
            default => throw new \LogicException('This code should not be reached!')
        };

        return $this->security->isGranted($role, $subject) || $this->security->isGranted(self::PAGE_ALL, $subject);
    }
}
