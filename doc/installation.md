networking init CMS
===================

**IMPORTANT NOTICE** This project is very much in an Alpha stage.
It is not advisable to use this for a production web site.

If you would like to git up and running with a CMS in you Symfony
application you can either install this bundle in an  existing project
or download and install the [networking init CMS sandbox][2]. If you are new to the init CMS we recommend you install the [networking init CMS sandbox][2] first.

1) Installing the networking init CMS bundle
--------------------------------------------
### Install using flex

This bundle comes with a flex recipe. To allow easy installation and initial configuration, please run:
    
    composer config extra.symfony.allow-contrib true

### Use Composer to install dependencies

Run the composer require command:

	php composer.phar require networking/init-cms-bundle

You will be prompted to specify a version constraint,

	Please provide a version constraint for the networking/init-cms-bundle requirement:


This will install the init cms bundle and all its dependencies in your
vendor folder, and add the bundle to the list of requirements in your composer.json

### Load Bundle

If you are not using flex, you will need to add the bundle to your app/AppKernel.php (symfony 3) or config/Bundles.php (symfony 4)

#### Symfony 3
```
	<?php
	// app/AppKernel.php
	public function registerbundles()
	{
	    $bundles = array(
                    new Symfony\Bundle\AclBundle\AclBundle(),
                    new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
                    new Symfony\Bundle\SecurityBundle\SecurityBundle(),
                    new Symfony\Bundle\TwigBundle\TwigBundle(),
                    new Symfony\Bundle\MonologBundle\MonologBundle(),
                    new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
                    new Symfony\Bundle\AsseticBundle\AsseticBundle(),
                    new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
                    new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
                    // these are the bundles for the CMS
        
                    new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
                    new JMS\SerializerBundle\JMSSerializerBundle(),
                    new Knp\Bundle\MenuBundle\KnpMenuBundle(),
                    new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
                    new Mopa\Bundle\BootstrapBundle\MopaBootstrapBundle(),
                    new FOS\UserBundle\FOSUserBundle(),
                    new Sonata\AdminBundle\SonataAdminBundle(),
                    new JMS\AopBundle\JMSAopBundle(),
                    new Sonata\UserBundle\SonataUserBundle(),
                    new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
                    new Sonata\BlockBundle\SonataBlockBundle(),
                    new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
                    new Sonata\MediaBundle\SonataMediaBundle(),
                    new Knp\Bundle\MarkdownBundle\KnpMarkdownBundle(),
                    new FOS\CKEditorBundle\FOSCKEditorBundle(),
                    new Sonata\FormatterBundle\SonataFormatterBundle(),
                    new \Sonata\IntlBundle\SonataIntlBundle(),
                    new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
                    new Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle(),
                    new Lexik\Bundle\TranslationBundle\LexikTranslationBundle(),
                    new Oneup\UploaderBundle\OneupUploaderBundle(),
                    new Networking\InitCmsBundle\NetworkingInitCmsBundle(),
                    new FOS\RestBundle\FOSRestBundle(),
                );
	}
```

#### Symfony 4 
```
    <?php
    
    return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    Symfony\Bundle\WebServerBundle\WebServerBundle::class => ['dev' => true],
    Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle::class => ['all' => true],
    Symfony\WebpackEncoreBundle\WebpackEncoreBundle::class => ['all' => true],
    Symfony\Bundle\AclBundle\AclBundle::class => ['all' => true],
    Sonata\DatagridBundle\SonataDatagridBundle::class => ['all' => true],
    Sonata\BlockBundle\SonataBlockBundle::class => ['all' => true],
    Knp\Bundle\MenuBundle\KnpMenuBundle::class => ['all' => true],
    Sonata\AdminBundle\SonataAdminBundle::class => ['all' => true],
    FOS\UserBundle\FOSUserBundle::class => ['all' => true],
    Sonata\UserBundle\SonataUserBundle::class => ['all' => true],
    Sonata\NotificationBundle\SonataNotificationBundle::class => ['all' => true],
    JMS\SerializerBundle\JMSSerializerBundle::class => ['all' => true],
    Sonata\MediaBundle\SonataMediaBundle::class => ['all' => true],
    Sonata\IntlBundle\SonataIntlBundle::class => ['all' => true],
    FOS\CKEditorBundle\FOSCKEditorBundle::class => ['all' => true],
    Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle::class => ['all' => true],
    Sonata\Doctrine\Bridge\Symfony\SonataDoctrineBundle::class => ['all' => true],
    Mopa\Bundle\BootstrapBundle\MopaBootstrapBundle::class => ['all' => true],
    Oneup\UploaderBundle\OneupUploaderBundle::class => ['all' => true],
    Oneup\FlysystemBundle\OneupFlysystemBundle::class => ['all' => true],
    Knp\Bundle\PaginatorBundle\KnpPaginatorBundle::class => ['all' => true],
    Lexik\Bundle\TranslationBundle\LexikTranslationBundle::class => ['all' => true],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['all' => true],
    Networking\InitCmsBundle\NetworkingInitCmsBundle::class => ['all' => true],
    Knp\Bundle\MarkdownBundle\KnpMarkdownBundle::class => ['all' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
    ];
```

### Configure the dependent user bundles
You will need to provide configuration for the SonataAdminBundle, LexikTranslationBundle, FOSUserBundle and the SonataUserBundle
in your app/config/config.yaml file.

A simple configuration should look something like the following, with special attention paid to the sonata_user configuration
as this will create the admin interface for administering users in the cms.


```
sonata_admin:
    title:      Demo Sailing Club
    options:
        use_select2: false

lexik_translation:
    fallback_locale: en      # (required) default locale to use
    managed_locales: [en, de]    # (required) locales that the bundle have to manage

fos_user:
    db_driver: orm
    firewall_name:  main
    user_class:     App\Entity\User
    group:
        group_class: Networking\InitCmsBundle\Entity\Group
    from_email:
            address:        webmaster@example.com
            sender_name:    net working Team

sonata_media:
    class:
        media:              Networking\InitCmsBundle\Entity\Media
        gallery:            Networking\InitCmsBundle\Entity\Gallery
        gallery_has_media:  Networking\InitCmsBundle\Entity\GalleryHasMedia
    default_context: default
    db_driver: doctrine_orm # or doctrine_mongodb
    contexts:
        default:
            download:
                strategy: sonata.media.security.public_strategy
                mode: http
            providers:
                - sonata.media.provider.image
                - sonata.media.provider.file
                - sonata.media.provider.youtube

            formats:
                small: { width: 100 , quality: 70}
                medium: { width: 400, height: 300 , quality: 70}
                big:   { width: 800, quality: 70}
                admin: { width: 100, quality: 70}
    cdn:
        server:
            path: /uploads/media # http://media.sonata-project.org/

    filesystem:
        local:
            directory:  "%kernel.project_dir%/public/uploads/media 
            create:     false
```

2) Configure the init CMS
-------------------------

### Configure CMS config.yaml

The CMS uses many different bundles which all require specific configurations. You can easily import our main
cms_config.ym file into your projects config.yaml which will overwrite many of your projects configurations and
insert the necessary configuration to get the project running (with exception to assetic which must be manually
entered).

Just replace the following line
```	imports:
	 ....
	 - { resource: security.yaml }
	 ...
```
with
```
	- { resource: @NetworkingInitCmsBundle/Resources/config/cms/config.yaml }
```

Alternatively you can view all the individual config files and manually insert the configuration into your project.

### Configure Doctrine

Now we need copy the contents of the @NetworkingInitCmsBundle/Resources/config/cms/doctrine.yaml file into your config.yaml
file, this is important as it contains information about entity mappings and behaviours


### Configure Routing

Insert the following config into your routing.yaml to include the init CMS routes.

	NetworkingInitCmsBundle:
        resource: "@NetworkingInitCmsBundle/Resources/config/routing.yaml"
        prefix:   /

### Enable translations

In order for the translations to work it is important to activate the translations in the framework configuration.

Comment in the translator parameter in your config.yaml file:

	#config.yaml
	framework:
        #esi:             ~
        translator:      { fallback: "%env(LOCALE)%" }



If they are not already created, you need to add specific folder to allow uploads from users:

.. code-block:: sh

    mkdir public/uploads
    mkdir public/uploads/media
    chmod -R 0777 public/uploads

3) Install assets
-----------------
```bash
   php bin/console mopa:bootstrap:symlink:less
```

4) Create DB schema, insert admin user and insert fixtures
----------------------------------------------------------

**IMPORTANT NOTE** Backup your DB before the next step

Now that the symfony application is more or less setup, it is time to load the CMS DBs and
fixtures, as well as create an admin user.

There is an install wizard which will get this done for you, just go to the following URL and follow the instructions:

    http://localhost//app_dev.php/cms_install


Alternatively you can run the install process on the command line,
you will be prompted to enter a username, email address and password, these will get you into the backend.

	php bin/console networking:initcms:install

Now you should be up and running.

If you decided to let composer install twitters bootstrap, you might want to activate auto symlinking and checking, after composer update/install.
So add this to your existing scripts section in your composer json:
(recommended!)

   ```json
   {
       "scripts": {
           "post-install-cmd": [
               "Mopa\\Bundle\\BootstrapBundle\\Composer\\ScriptHandler::postInstallSymlinkTwitterBootstrap"
           ],
           "post-update-cmd": [
               "Mopa\\Bundle\\BootstrapBundle\\Composer\\ScriptHandler::postInstallSymlinkTwitterBootstrap"
           ]
       }
   }
   ```

There is also a console command to check and / or install this symlink:

   ```bash
   php bin/console mopa:bootstrap:symlink:less
   ```

With these steps taken, bootstrap should be install into vendor/twitter/bootstrap/ and a symlink
been created into vendor/mopa/bootstrap-bundle/Resources/bootstrap.

6) You have installed the init CMS bundle
-----------------------------------------
Then you can visit your admin dashboard on http://my-server/admin/dashboard

[1]:  http://www.networking.ch
[2]:  https://github.com/networking/init-cms-sandbox/
[3]:  https://github.com/phiamo/MopaBootstrapBundle
[4]:  http://sonata-project.org/bundles/user/master/doc/reference/installation.html#configuration
[5]:  http://sonata-project.org/bundles/media/master/doc/reference/installation.html#configuration
