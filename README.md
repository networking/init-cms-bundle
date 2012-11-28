networking init CMS
===================

**IMPORTANT NOTICE: THIS PROJECT IS CURRENTLY IN A DEVELOPPMENT STAGE - THE README DOCUMENTATION IS CURRENTLY
NOT CORRECT.**



This bundle forms the basis of the networking init CMS, The project is being
developed by the small hard working team at [net working AG][1] in Zürich.

**IMPORTANT NOTICE** This project is very much in an Alpha stage.
It is not advisable to use this for a production web site.

If you would like to git up and  running with a CMS in you Symfony
application you can either install this bundle in an  existing project
or download and install the [networking init CMS sandbox][2]

1) Installing the networking init CMS bundle
--------------------------------------------

### Use Composer to install dependencies


Add the following to the end of you composer.json, before the
last closing } (curly brace). This will tell composer where to find the bundle.

This is just a temporary measure until we add the package to packagist.org

    "repositories":[
        {
            "type":"package",
            "package":{
                "version":"master",
                "name":"twitter/bootstrap",
                "source":{
                    "url":"https://github.com/twitter/bootstrap.git",
                    "type":"git",
                    "reference":"master"
                }
            }
        },
        {
            "url":"git@github.com:networking/init-cms-bundle.git",
            "type":"vcs"
        }
    ]

Next run the composer require command:

	composer require networking/init-cms-bundle

This will install the init cms bundle and all its dependencies in your
vendor folder.

### Add to AppKernel

Add the following lines to your application kernel

	<?php
	// app/appkernel.php
	public function registerbundles()
	{
	    return array(
	        // ...
	        new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
	        new JMS\SerializerBundle\JMSSerializerBundle($this),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Knp\Bundle\MarkdownBundle\KnpMarkdownBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new Mopa\Bundle\BootstrapBundle\MopaBootstrapBundle(),
            new Mopa\Bundle\RemoteUpdateBundle\MopaRemoteUpdateBundle(),
            new Mopa\Bundle\WSSEAuthenticationBundle\MopaWSSEAuthenticationBundle(),
            new Sensio\Bundle\BuzzBundle\SensioBuzzBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new Liip\ThemeBundle\LiipThemeBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Sonata\jQueryBundle\SonatajQueryBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\CacheBundle\SonataCacheBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\UserBundle\SonataUserBundle('FOSUserBundle'),
            new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
            new Sonata\FormatterBundle\SonataFormatterBundle(),
            new Sonata\MediaBundle\SonataMediaBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Symfony\Cmf\Bundle\RoutingExtraBundle\SymfonyCmfRoutingExtraBundle(),
            new Networking\InitCmsBundle\NetworkingInitCmsBundle(),
	        // ...
	    );
	}

### Configure and extend the user bundle

This part is taken straight from the SonataUserBundle [configuration][3], here it is again
word for word:

When using ACL, the UserBundle can prevent ``normal`` user to change settings
of ``super-admin`` users, to enable this add to the configuration:

.. code-block:: yaml

    # app/config/config.yml
    sonata_user:
        security_acl: true


    # app/config/security.yml
    security:
        # [...]
        acl:
            connection: default

Doctrine Configuration
~~~~~~~~~~~~~~~~~~~~~~
Then add these bundles in the config mapping definition (or enable `auto_mapping <http://symfony.com/doc/2.0/reference/configuration/doctrine.html#configuration-overview>`_):

.. code-block:: yaml

    # app/config/config.yml

    fos_user:
        db_driver:      orm # can be orm or odm
        firewall_name:  main
        user_class:     Application\Sonata\UserBundle\Entity\User

        group:
            group_class: Application\Sonata\UserBundle\Entity\Group

    doctrine:
        orm:
            entity_managers:
                default:
                    mappings:
                        ApplicationSonataUserBundle: ~
                        SonataUserBundle: ~

        dbal:
            types:
                json: Sonata\Doctrine\Types\JsonType

Integrating the bundle into the Sonata Admin Bundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Add the related security routing information

.. code-block:: yaml

    sonata_user:
        resource: '@SonataUserBundle/Resources/config/routing/admin_security.xml'
        prefix: /admin

Then add a new custom firewall handlers for the admin

.. code-block:: yaml

    security:
        role_hierarchy:
            ROLE_ADMIN:       [ROLE_USER, ROLE_SONATA_ADMIN]
            ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]
            SONATA:
                - ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT  # if you are using acl then this line must be commented

        providers:
            fos_userbundle:
                id: fos_user.user_manager

        firewalls:
            # -> custom firewall for the admin area of the URL
            admin:
                switch_user:        true
                context:            user
                pattern:            /admin(.*)
                form_login:
                    provider:       fos_userbundle
                    login_path:     /admin/login
                    use_forward:    false
                    check_path:     /admin/login_check
                    failure_path:   null
                    use_referer:    true
                logout:
                    path:           /admin/logout
                    target:         /admin/login

                anonymous:    true
            # -> end custom configuration

            # defaut login area for standard users
            main:
                switch_user:        true
                context:            user
                pattern:            .*
                form_login:
                    provider:       fos_userbundle
                    login_path:     /login
                    use_forward:    false
                    check_path:     /login_check
                    failure_path:   null
                logout:             true
                anonymous:          true

The last part is to define 3 new access control rules :

.. code-block:: yaml

    security:
        access_control:
            # URL of FOSUserBundle which need to be available to anonymous users
            - { path: ^/_wdt, role: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/_profiler, role: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/login$, role: IS_AUTHENTICATED_ANONYMOUSLY }

            # -> custom access control for the admin area of the URL
            - { path: ^/admin/login$, role: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/admin/logout$, role: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/admin/login-check$, role: IS_AUTHENTICATED_ANONYMOUSLY }
            # -> end

            - { path: ^/register, role: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/resetting, role: IS_AUTHENTICATED_ANONYMOUSLY }

            # Secured part of the site
            # This config requires being logged for the whole site and having the admin role for the admin part.
            # Change these rules to adapt them to your needs
            - { path: ^/admin, role: [ROLE_ADMIN, ROLE_SONATA_ADMIN] }
            - { path: ^/.*, role: IS_AUTHENTICATED_ANONYMOUSLY }


Using the roles
---------------

Each admin has its own roles, use the user form to assign them to other users.
The available roles to assign to others are limited to the roles available to
the user editing the form.

Extending the Bundle
--------------------
At this point, the bundle is functionnal, but not quite ready yet. You need to
generate the correct entities for the media::

    php app/console sonata:easy-extends:generate SonataUserBundle

If you specify no parameter, the files are generated in app/Application/Sonata...
but you can specify the path with ``--dest=src``

.. note::

    The command will generate domain objects in an ``Application`` namespace.
    So you can point entities' associations to a global and common namespace.
    This will make Entities sharing easier as your models will allow to
    point to a global namespace. For instance the user will be
    ``Application\Sonata\UserBundle\Entity\User``.

Now, add the new `Application` Bundle into the kernel:

.. code-block:: php

    <?php

    // AppKernel.php
    class AppKernel {
        public function registerbundles()
        {
            return array(
                // Application Bundles
                // ...
                new Application\Sonata\UserBundle\ApplicationSonataUserBundle(),
                // ...

            )
        }
    }

### Configure and extend the media bundle

We have to do pretty much the same for the media bundle, this is taken from the [SonataMediaBundle configuration][4]

First we add the routing to the routing.yml file

.. code-block:: yaml

    gallery:
        resource: '@SonataMediaBundle/Resources/config/routing/gallery.xml'
        prefix: /media/gallery

    media:
        resource: '@SonataMediaBundle/Resources/config/routing/media.xml'
        prefix: /media


Then you must configure the interaction with the orm and add the mediaBundles settings:

.. code-block:: yaml

    # app/config/config.yml

    doctrine:
        orm:
            entity_managers:
                default:
                    mappings:
                        SonataMediaBundle: ~

    sonata_media:
        default_context: default
        db_driver: doctrine_orm # or doctrine_mongodb
        contexts:
            default:  # the default context is mandatory
                providers:
                    - sonata.media.provider.dailymotion
                    - sonata.media.provider.youtube
                    - sonata.media.provider.image
                    - sonata.media.provider.file

                formats:
                    small: { width: 100 , quality: 70}
                    big:   { width: 500 , quality: 70}

        cdn:
            server:
                path: /uploads/media # http://media.sonata-project.org/

        filesystem:
            local:
                directory:  %kernel.root_dir%/../web/uploads/media
                create:     false

.. note::

    You can define formats per provider type. You might want to set
    a transversal ``admin`` format to be used by the ``mediaadmin`` class.

Also, you can determine the resizer to use; the default value is
``sonata.media.resizer.simple`` but you can change it to ``sonata.media.resizer.square``

.. code-block:: yaml

    # app/config/config.yml

    sonata_media:
        providers:
            image:
                resizer: sonata.media.resizer.square

.. note::

    The square resizer works like the simple resizer when the image format has
    only the width. But if you specify the height the resizer crop the image in
    the lower size.

At this point, the bundle is not yet ready. You need to generate the correct
entities for the media::

    php app/console sonata:easy-extends:generate SonataMediaBundle

.. note::

    To be able to generate domain objects, you need to have a database driver configure in your project.
    If it's not the case, just follow this:
    http://symfony.com/doc/current/book/doctrine.html#configuring-the-database

.. note::

    The command will generate domain objects in an ``Application`` namespace.
    So you can point entities' associations to a global and common namespace.
    This will make Entities sharing very easier as your models will allow to
    point to a global namespace. For instance the media will be
    ``Application\Sonata\MediaBundle\Entity\Media``.


Now that your module is generated, you can register it

.. code-block:: php

    <?php
    // app/appkernel.php
    public function registerbundles()
    {
        return array(
            ...
            new Application\Sonata\MediaBundle\ApplicationSonataMediaBundle(),
            ...
        );
    }

    # app/config/config.yml
      doctrine:
          orm:
              entity_managers:
                  default:
                      mappings:
                          ApplicationSonataMediaBundle: ~


Now, you can build up your database:

.. code-block:: sh

    app/console doctrine:schema:[create|update]


If they are not already created, you need to add specific folder to allow uploads from users:

.. code-block:: sh

    mkdir web/uploads
    mkdir web/uploads/media
    chmod -R 0777 web/uploads

Then you can visit your admin dashboard on http://my-server/admin/dashboard
[1]:  http://web.networking.ch
[2]:  https://github.com/networking/init-cms-sandbox/
[3]:  http://sonata-project.org/bundles/user/master/doc/reference/installation.html#configuration