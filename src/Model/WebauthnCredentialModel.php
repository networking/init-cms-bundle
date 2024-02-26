<?php

namespace Networking\InitCmsBundle\Model;

use Webauthn\PublicKeyCredentialSource;

if (class_exists(PublicKeyCredentialSource::class)) {
    class WebauthnCredentialModel extends PublicKeyCredentialSource
    {
    }
} else {
    class WebauthnCredentialModel
    {
    }
}
