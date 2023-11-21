<?php

namespace Networking\InitCmsBundle\Repository;


use Networking\InitCmsBundle\Entity\BaseUser as User;
use Networking\InitCmsBundle\Entity\UserManager;
use Symfony\Component\Uid\Uuid;
use Webauthn\Bundle\Repository\CanGenerateUserEntity;
use Webauthn\Bundle\Repository\CanRegisterUserEntity;
use Webauthn\Bundle\Repository\PublicKeyCredentialUserEntityRepositoryInterface;
use Webauthn\PublicKeyCredentialUserEntity;

final class WebauthnUserEntityRepository implements PublicKeyCredentialUserEntityRepositoryInterface, CanGenerateUserEntity, CanRegisterUserEntity
{
    /**
     * The UserRepository $userRepository is the repository
     * that already exists in the application
     */
    public function __construct(private UserManager $userRepository)
    {
    }

    public function findOneByUsername(string $username): ?PublicKeyCredentialUserEntity
    {
        /** @var User|null $user */
        $user = $this->userRepository->findOneBy([
            'username' => $username,
        ]);

        return $this->getUserEntity($user);
    }

    public function findOneByUserHandle(string $userHandle): ?PublicKeyCredentialUserEntity
    {
        /** @var User|null $user */
        $user = $this->userRepository->findOneBy([
            'username' => $userHandle,
        ]);

        return $this->getUserEntity($user);
    }

    /**
     * Converts a Symfony User (if any) into a Webauthn User Entity
     */
    private function getUserEntity(null|User $user): ?PublicKeyCredentialUserEntity
    {
        if ($user === null) {
            return null;
        }

        return new PublicKeyCredentialUserEntity(
            $user->getUsername(),
            $user->getUserIdentifier(),
            $user->getDisplayName(),
            null
        );
    }


    public function generateUserEntity(
        ?string $username,
        ?string $displayName
    ): PublicKeyCredentialUserEntity {
        return PublicKeyCredentialUserEntity::create(
            $username,
            Uuid::v4()->toRfc4122(),
            $displayName
        );
    }

    public function generateNextUserEntityId(): string
    {
        return Uuid::v4()->toRfc4122();
    }

    public function saveUserEntity(PublicKeyCredentialUserEntity $userEntity
    ): void {
        // TODO: Implement saveUserEntity() method.
    }
}