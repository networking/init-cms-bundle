<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Repository;

use Networking\InitCmsBundle\Entity\WebauthnCredential;
use Doctrine\Persistence\ManagerRegistry;
use Webauthn\Bundle\Repository\DoctrineCredentialSourceRepository;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

final class WebauthnCredentialRepository extends DoctrineCredentialSourceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebauthnCredential::class);
    }

    public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource, ?array $otherUi = null): void
    {
        if (!$publicKeyCredentialSource instanceof WebauthnCredential) {
            $publicKeyCredentialSource = new WebauthnCredential(
                $publicKeyCredentialSource->publicKeyCredentialId,
                $publicKeyCredentialSource->type,
                $publicKeyCredentialSource->transports,
                $publicKeyCredentialSource->attestationType,
                $publicKeyCredentialSource->trustPath,
                $publicKeyCredentialSource->aaguid,
                $publicKeyCredentialSource->credentialPublicKey,
                $publicKeyCredentialSource->userHandle,
                $publicKeyCredentialSource->counter,
                $publicKeyCredentialSource->otherUI
            );
        }
        parent::saveCredentialSource($publicKeyCredentialSource);
    }

    public function findAllForUserEntityAndAaguid(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity, $aaguid) : array{
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->from($this->class, 'c')
            ->select('c')
            ->where('c.userHandle = :userHandle')
            ->andWhere('c.aaguid = :aaguid')
            ->setParameter(':userHandle', $publicKeyCredentialUserEntity->id)
            ->setParameter(':aaguid', $aaguid)
            ->getQuery()
            ->execute();
    }

    public function removeCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void
    {
        $this->getEntityManager()->remove($publicKeyCredentialSource);
        $this->getEntityManager()->flush();
    }
}