<?php

namespace Networking\InitCmsBundle\Model;

use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Ulid;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TrustPath\TrustPath;

if(class_exists(PublicKeyCredentialSource::class)){
    class WebauthnCredentialModel extends PublicKeyCredentialSource
    {
        public function __construct(
            string $publicKeyCredentialId,
            string $type,
            array $transports,
            string $attestationType,
            TrustPath $trustPath,
            AbstractUid $aaguid,
            string $credentialPublicKey,
            string $userHandle,
            int $counter,
            ?array $otherUi
        ) {
            $this->id = Ulid::generate();
            parent::__construct(
                $publicKeyCredentialId,
                $type,
                $transports,
                $attestationType,
                $trustPath,
                $aaguid,
                $credentialPublicKey,
                $userHandle,
                $counter,
                $otherUi
            );
        }
    }
}else {
    class WebauthnCredentialModel
    {

    }
}
