Webauthn and Passkey login
==========================

The networking initcms supports the [Webauthn](https://webauthn.io/) standard for passwordless login. 
This is a more secure way of logging in to your cms, as it does not require a password, and is based on a public/private key pair,
which is stored in your browser, on your computer, or on your phone, and requires some form of biometric authentication to login.

In order to use this feature, you need to have a compatible browser, and a compatible device, such as a Yubikey, or a phone with a fingerprint reader.

To enable this feature, you need to require the [web-auth/webauthn-symfony-bundle](https://github.com/web-auth) bundle:

```bash
composer require web-auth/webauthn-symfony-bundle
```

configure the webauthn.yaml file and add the following:

```yaml
webauthn:
    credential_repository: 'Networking\InitCmsBundle\Repository\WebauthnCredentialRepository' # CREATE YOUR REPOSITORY AND CHANGE THIS!
    user_repository: 'Networking\InitCmsBundle\Repository\WebauthnUserEntityRepository' # CREATE YOUR REPOSITORY AND CHANGE THIS!
    controllers:
        enabled: true
    creation_profiles:
        default:
            public_key_credential_parameters:
                - !php/const Cose\Algorithms::COSE_ALGORITHM_ES256
                - !php/const Cose\Algorithms::COSE_ALGORITHM_EDDSA
                - !php/const Cose\Algorithms::COSE_ALGORITHM_RS256
            authenticator_selection_criteria:
                attachment_mode: !php/const Webauthn\AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_CROSS_PLATFORM
            rp:
                name: '%env(RELYING_PARTY_NAME)%' # Please adapt the env file with the correct relaying party ID or set null
                id: '%env(RELYING_PARTY_ID)%' # Please adapt the env file with the correct relaying party ID or set null
    request_profiles:
        default:
            rp_id: '%env(RELYING_PARTY_ID)%' # Please adapt the env file with the correct relaying party ID or set null
```

Add the routes for the webauthn bundle to your `config/routes/webauthn.yaml` file:

```yaml
webauthn_routes:
    resource: .
    type: webauthn

app_auth_register_request:
    resource: '@NetworkingInitCmsBundle/Controller/WebAuthn/WebAuthnController.php'
    type: attribute
```

Then you need to add the following to your `config/packages/security.yaml` file:


```yaml
security:
    firewalls:
        admin:
            #......
            webauthn:
                registration:
                    enabled: true
                    routes:
                        options_path: '/admin/register/options'
                authentication:
                    enabled: true
                    routes:
                        options_path: '/admin/assertion/options'
                        result_path: '/admin/assertion'
                        
    #......
    access_control:
        - { path: ^/admin/assertion,  roles: PUBLIC_ACCESS, requires_channel: 'https' }                    
            
```

Enable webauthn in the networking initcms bundle by adding the following to your `config/packages/networking_initcms.yaml` file:

```yaml
networking_init_cms:
    #......
    webauthn:
        enabled: true
```

And finally, you need to update your database schema:

```bash
bin/console doctrine:schema:update --force
```

Or if you are using migrations:

```bash
bin/console make:migration
bin/console doctrine:migrations:migrate
```

Once you have done this, users can setup webauthn by going to the `/admin/cms/users/profile/security` account settings page.
Just follow the instructions on the page to setup webauthn, in the Tab "Passkeys".