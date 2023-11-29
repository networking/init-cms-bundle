<?php
declare(strict_types=1);

namespace Networking\InitCmsBundle\Entity;

use Networking\InitCmsBundle\Model\WebauthnCredentialModel;
use Networking\InitCmsBundle\Repository\WebauthnCredentialRepository;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;


#[Table(name: "webauthn_credentials")]
#[Entity(repositoryClass: WebauthnCredentialRepository::class)]
class WebauthnCredential extends WebauthnCredentialModel
{
    #[Id]
    #[Column(unique: true)]
    #[GeneratedValue(strategy: "NONE")]
    private string $id;



    public function getId(): string
    {
        return $this->id;
    }
}
