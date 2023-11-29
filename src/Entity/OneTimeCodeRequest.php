<?php

namespace Networking\InitCmsBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResetPasswordRequestRepository::class)]
class OneTimeCodeRequest
{


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private $userHandle;

    #[ORM\Column(type: Types::STRING, length: 20)]
    protected $code;

    #[ORM\Column(type: Types::STRING, length: 100)]
    protected $hashedToken;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected $requestedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected $expiresAt;

    public function __construct(BaseUser $user, \DateTimeInterface $expiresAt, string $code, string $hashedToken)
    {
        $this->userHandle = $user->getUserIdentifier();
        $this->initialize($expiresAt, $code, $hashedToken);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserHandle(): string
    {
        return $this->userHandle;
    }

    protected function initialize(\DateTimeInterface $expiresAt, string $code, string $hashedToken)
    {
        $this->requestedAt = new \DateTimeImmutable('now');
        $this->expiresAt = $expiresAt;
        $this->code = $code;
        $this->hashedToken = $hashedToken;
    }

    public function getRequestedAt(): \DateTimeInterface
    {
        return $this->requestedAt;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt->getTimestamp() <= time();
    }

    public function getExpiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function getHashedToken(): string
    {
        return $this->hashedToken;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
