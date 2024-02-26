<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\Table;
use Networking\InitCmsBundle\Model\WebauthnCredentialModel;
use Networking\InitCmsBundle\Repository\WebauthnCredentialRepository;
use Symfony\Component\Uid\Ulid;

#[Table(name: 'webauthn_credentials')]
#[Entity(repositoryClass: WebauthnCredentialRepository::class)]
#[HasLifecycleCallbacks]
class WebauthnCredential extends WebauthnCredentialModel
{
    #[Id]
    #[Column(unique: true)]
    #[GeneratedValue(strategy: 'NONE')]
    protected ?string $id = null;

    public ?bool $backupEligible = null;

    public ?bool $backupStatus = null;

    public ?bool $uvInitialized = null;

    #[PrePersist]
    public function prePersist(): void
    {
        if (null === $this->id) {
            $this->id = Ulid::generate();
        }
    }

    public function getId(): string
    {
        return $this->id;
    }
}
