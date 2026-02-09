<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Cose\Algorithms;
use Webauthn\AuthenticatorSelectionCriteria;

return static function (ContainerConfigurator $container): void {
    $container->extension('webauthn', [
        'credential_repository' => 'Networking\\InitCmsBundle\\Repository\\WebauthnCredentialRepository',
        'user_repository' => 'Networking\\InitCmsBundle\\Repository\\WebauthnUserEntityRepository',
        'controllers' => [
            'enabled' => true,
        ],
        'creation_profiles' => [
            'default' => [
                'public_key_credential_parameters' => [
                    Algorithms::COSE_ALGORITHM_ES256,
                    Algorithms::COSE_ALGORITHM_EDDSA,
                    Algorithms::COSE_ALGORITHM_RS256,
                ],
                'authenticator_selection_criteria' => [
                    'attachment_mode' => AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_CROSS_PLATFORM,
                ],
                'rp' => [
                    'name' => '%env(RELYING_PARTY_NAME)%',
                    'id' => '%env(RELYING_PARTY_ID)%',
                ],
            ],
        ],
        'request_profiles' => [
            'default' => [
                'rp_id' => '%env(RELYING_PARTY_ID)%',
            ],
        ],
    ]);
};
