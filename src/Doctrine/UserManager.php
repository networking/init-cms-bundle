<?php

declare(strict_types=1);

/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Doctrine;

use Doctrine\Persistence\ManagerRegistry;
use Sonata\Doctrine\Entity\BaseEntityManager;
use Sonata\UserBundle\Model\UserInterface;
use Sonata\UserBundle\Util\CanonicalFieldsUpdaterInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sonata\UserBundle\Model\UserManagerInterface;
/**
 * Class UserManager.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class UserManager extends BaseEntityManager implements UserManagerInterface
{
    /**
     * TODO: Simplify this once support for Symfony 4.4 is dropped.
     *
     * @psalm-suppress UndefinedDocblockClass
     *
     * @phpstan-param class-string<UserInterface> $class
     *
     * @param UserPasswordEncoderInterface|UserPasswordHasherInterface $userPasswordHasher
     */
    public function __construct(
        string $class,
        ManagerRegistry $registry,
        private readonly CanonicalFieldsUpdaterInterface $canonicalFieldsUpdater,
        // @phpstan-ignore-next-line
        private readonly object $userPasswordHasher
    ) {
        parent::__construct($class, $registry);
    }

    /**
     * @psalm-suppress UndefinedDocblockClass
     */
    public function updatePassword(UserInterface $user): void
    {
        $plainPassword = $user->getPlainPassword();

        if (null === $plainPassword) {
            return;
        }

        if ($this->userPasswordHasher instanceof UserPasswordHasherInterface) {
            $password = $this->userPasswordHasher->hashPassword($user, $plainPassword);
        } else {
            // @phpstan-ignore-next-line
            $password = $this->userPasswordHasher->encodePassword($user, $plainPassword);
        }

        $user->setPassword($password);
        $user->eraseCredentials();
    }

    public function findUserByUsername(string $username): ?UserInterface
    {
        return $this->findOneBy([
            'usernameCanonical' => $this->canonicalFieldsUpdater->canonicalizeUsername($username),
        ]);
    }

    public function findUserByEmail(string $email): ?UserInterface
    {
        return $this->findOneBy([
            'emailCanonical' => $this->canonicalFieldsUpdater->canonicalizeEmail($email),
        ]);
    }

    public function findUserByUsernameOrEmail(string $usernameOrEmail): ?UserInterface
    {
        if (1 === preg_match('/^.+\@\S+\.\S+$/', $usernameOrEmail)) {
            $user = $this->findUserByEmail($usernameOrEmail);
            if (null !== $user) {
                return $user;
            }
        }

        return $this->findUserByUsername($usernameOrEmail);
    }

    public function findUserByConfirmationToken(string $token): ?UserInterface
    {
        return $this->findOneBy(['confirmationToken' => $token]);
    }
}
