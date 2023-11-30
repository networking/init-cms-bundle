Two-Factor Authentication
==========================

This bundle provides a two factor authentication for the admin interface.

To enable it, all you need to do is set up two-factor authentication in the  `config/packages/networking_init_cms.yaml` file:

```yaml
networking_init_cms:
    #......
    2fa_authenticator:
        enabled: true
        server: 'example.com'
        trusted_ip_list:
            - 127.0.0.1
        forced_for_role:
            - ROLE_SONATA_ADMIN
            - ROLE_SUPER_ADMIN
```

The `server` option is used to set the server name for the QR code. This is should be the same domain name as the one used to access the admin interface.

The `trusted_ip_list` option is used to set a list of IP addresses that are allowed to access the admin interface without two-factor authentication.

The `forced_for_role` option is used to set a list of roles that are forced to use two-factor authentication.

When a user logs in for the first time, they will be asked to set up two-factor authentication. 
They will be asked to scan a QR code with their authenticator app. 
Once they have done this, they will be asked to enter a code from their authenticator app, after which they will be logged in.

If a user has set up two-factor authentication, they will be asked to enter a code from their authenticator app after 
they have entered their username and password, every time they log in.

If, for some reason, they do not have access to their authenticator app, they can click on the 
"Don't have your authenticator app? Send a code by email" link. This will send them an email with a single code that 
they can use to log in.
