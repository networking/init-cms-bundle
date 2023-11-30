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

Make sure that the bundles have been added to your config/bundles.php file:

```
<?php
// config/bundles.php

    return [
        Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
        Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
        Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
        Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true],
        Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
        Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
        Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
        Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
        Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
        Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
        Knp\Bundle\MenuBundle\KnpMenuBundle::class => ['all' => true],
        Symfony\WebpackEncoreBundle\WebpackEncoreBundle::class => ['all' => true],
        Symfony\Bundle\AclBundle\AclBundle::class => ['all' => true],
        Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle::class => ['all' => true],
        Sonata\Twig\Bridge\Symfony\SonataTwigBundle::class => ['all' => true],
        Sonata\Doctrine\Bridge\Symfony\SonataDoctrineBundle::class => ['all' => true],
        Sonata\Form\Bridge\Symfony\SonataFormBundle::class => ['all' => true],
        Sonata\UserBundle\SonataUserBundle::class => ['all' => true],
        Sonata\MediaBundle\SonataMediaBundle::class => ['all' => true],
        Sonata\Exporter\Bridge\Symfony\SonataExporterBundle::class => ['all' => true],
        Sonata\BlockBundle\SonataBlockBundle::class => ['all' => true],
        Sonata\AdminBundle\SonataAdminBundle::class => ['all' => true],
        Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle::class => ['all' => true],
        Oneup\UploaderBundle\OneupUploaderBundle::class => ['all' => true],
        Oneup\FlysystemBundle\OneupFlysystemBundle::class => ['all' => true],
        Lexik\Bundle\TranslationBundle\LexikTranslationBundle::class => ['all' => true],
        Knp\Bundle\PaginatorBundle\KnpPaginatorBundle::class => ['all' => true],
        FOS\CKEditorBundle\FOSCKEditorBundle::class => ['all' => true],
        Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
        Networking\InitCmsBundle\NetworkingInitCmsBundle::class => ['all' => true],
        Symfony\UX\Chartjs\ChartjsBundle::class => ['all' => true],
        Symfony\UX\Autocomplete\AutocompleteBundle::class => ['all' => true],
        Symfony\UX\TwigComponent\TwigComponentBundle::class => ['all' => true],
        Symfony\UX\LiveComponent\LiveComponentBundle::class => ['all' => true],
        Symfony\UX\Cropperjs\CropperjsBundle::class => ['all' => true],
        FOS\RestBundle\FOSRestBundle::class => ['all' => true],
        Networking\FormGeneratorBundle\NetworkingFormGeneratorBundle::class => ['all' => true],
        FOS\ElasticaBundle\FOSElasticaBundle::class => ['all' => true],
        Networking\ElasticSearchBundle\NetworkingElasticSearchBundle::class => ['all' => true],
        Symfony\UX\StimulusBundle\StimulusBundle::class => ['all' => true],
        FOS\JsRoutingBundle\FOSJsRoutingBundle::class => ['all' => true],
        Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle::class => ['all' => true],
        //Comment out when enabling the web-authn/webauthn-symfony-bundle
        //SpomkyLabs\CborBundle\SpomkyLabsCborBundle::class => ['all' => true],
        //Webauthn\Bundle\WebauthnBundle::class => ['all' => true],
    ];

	}
```



2) Configure the init CMS
-------------------------

### Configure CMS config.yaml

The CMS uses many different bundles which all require specific configurations.
The networking init CMS bundle comes with a config.yaml file which contains all the necessary configuration for the 
init CMS to run.

Assets
Doctrine
FosCKEditorBundle
LexikTranslationBundle
OneupUploaderBundle
OneupFlysystemBundle
SonataUserBundle
SonataMediaBundle
SonataAdminBundle
SonataDoctrineORMAdminBundle
SonataBlockBundle
SymfonyCmfRoutingBundle


Just replace the following line
```	imports:
	 ....
	 - { resource: @NetworkingInitCmsBundle/Resources/config/cms/config.yaml }
	 ...
```

You will also find a config file for security. You will need to copy the contents of this file into your security.yaml file, 
and then customise it to your needs.

Alternatively you can view all the individual config files and manually insert the configuration into your project.

### Configure Doctrine

As we use the gedmo/doctrine-extension package, you will need to add the following to your doctrine.yaml file, in order
to register the extensions:


```yaml
imports:
    - { resource: "../gedmo_doctrine_extensions.yaml" }
```

You will also need to add the mapping information for the gedmo extensions:

```yaml
doctrine:
    orm:
        entity_managers:
            mappings:
                gedmo_translator:
                type: attribute
                prefix: "Gedmo\\Translator\\Entity"
                dir: "%kernel.project_dir%/vendor/gedmo/doctrine-extensions/src/Translator/Entity"
                alias: GedmoTranslator # this one is optional and will default to the name set for the mapping
                is_bundle: false
                gedmo_loggable:
                    type: attribute
                    prefix: "Gedmo\\Loggable\\Entity"
                    dir: "%kernel.project_dir%/vendor/gedmo/doctrine-extensions/src/Loggable/Entity"
                    alias: GedmoLoggable # this one is optional and will default to the name set for the mapping
                    is_bundle: false
                gedmo_tree:
                    type: attribute
                    prefix: "Gedmo\\Tree\\Entity"
                    dir: "%kernel.project_dir%/vendor/gedmo/doctrine-extensions/src/Tree/Entity"
                    alias: GedmoTree # this one is optional and will default to the name set for the mapping
                    is_bundle: false
                gedmo_sortable:
                    type: attribute
                    prefix: "Gedmo\\Sortable\\Entity"
                    dir: "%kernel.project_dir%/vendor/gedmo/doctrine-extensions/src/Sortable/Entity"
                    alias: GedmoSortable # this one is optional and will default to the name set for the mapping
                    is_bundle: false
```
                

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

    http://localhost/cms_install


Alternatively you can run the install process on the command line,
you will be prompted to enter a username, email address and password, these will get you into the backend.

	php bin/console networking:initcms:install

Now you should be up and running.


5) You have installed the init CMS bundle
-----------------------------------------
Then you can visit your admin dashboard on http://my-server/admin/dashboard

[1]:  http://www.networking.ch
[2]:  https://github.com/networking/init-cms-sandbox/
[3]:  https://github.com/phiamo/MopaBootstrapBundle
[4]:  http://sonata-project.org/bundles/user/master/doc/reference/installation.html#configuration
[5]:  http://sonata-project.org/bundles/media/master/doc/reference/installation.html#configuration
