networking init CMS
===================

This bundle forms the basis of the networking init CMS, The project is being
developed by the small hard working team at [net working AG][1] in Zürich.

**IMPORTANT NOTICE** This project is very much in an Alpha stage.
It is not advisable to use this for a production web site.

If you would like to git up and running with a CMS in you Symfony
application you can either install this bundle in an  existing project
or download and install the [networking init CMS sandbox][2]

1) Installing the networking init CMS bundle
--------------------------------------------

### Use Composer to install dependencies


Add the following to the end of you composer.json, before the
last closing } (curly brace). This will tell composer where to find the twitter bootstrap repository.

    ,
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
        }
    ]

Next run the composer require command:

	php composer.phar require networking/init-cms-bundle

You will be prompted to specify a version constraint,

	Please provide a version constraint for the networking/init-cms-bundle requirement:

Please enter "dev-master" for the time being.

This will install the init cms bundle and all its dependencies in your
vendor folder, and add the bundle to the list of requirements in your composer.json

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

2) Configure the init CMS
-------------------------

### Configure CMS config.yml

The CMS uses many different bundles which all require specific configurations. You can easily import our main
cms_config.ym file into your projects config.yml which will overwrite many of your projects configurations and
insert the necessary configuration to get the project running (with exception to assetic which must be manually
entered).

Just replace the following line
```	imports:
	 ....
	 - { resource: security.yml }
	 ...
```
with
```
	- { resource: @NetworkingInitCmsBundle/Resources/config/cms/cms_config.yml }
```

Alternatively you can view all the individual config files and manually insert the configuration into your project.

### Configure Doctrine

Now we need copy the contents of the @NetworkingInitCmsBundle/Resources/config/cms/doctrine.yml file into your config.yml
file, this is important as it contains information about entity mappings and behaviours

### Configure Assetic (See [MopaBootstrapBundle][3])

If you are not using [symfony-bootstrap](http://github.com/phiamo/symfony-bootstrap) you must configure assetic to use less,
and make sure you have node installed.

Yui CSS and CSS Embed are very nice and recommended.
To make full use of bootstraps capabilites they are not needed, neither is less but its up to you

Here is an example configuration for your config.yml:
Make sure you have java installed

``` yaml
assetic:
    filters:
        less:
            node: /usr/local/bin/node
            node_paths: [/usr/local/lib/node_modules/]
            apply_to: "\.less$"
        cssrewrite: ~
        cssembed:
            jar: %kernel.root_dir%/Resources/java/cssembed-0.3.6.jar
            apply_to: "\.css$|\.less$"
        yui_css:
            jar: %kernel.root_dir%/Resources/java/yuicompressor-2.4.6.jar
            apply_to: "\.css$"
        yui_js:
            jar: %kernel.root_dir%/Resources/java/yuicompressor-2.4.6.jar
```

Do not forget to add the jars to your app.

If you encounter the following Error:

```
An exception has been thrown during the compilation of a template ("You must add MopaBootstrapBundle to the assetic.bundle config to use the {% stylesheets %} tag in MopaBootstrapBundle::base.html.twig.") in "/YourProject/vendor/mopa/bootstrap-bundle/Mopa/Bundle/BootstrapBundle/Resources/views/base.html.twig".
```

It's because the Bundle is not added to the bundles: [ ] config option in the assetic config.

``` yaml
assetic:
    debug:          %kernel.debug%
    use_controller: false
    bundles:        [ ] # <-
    filters:
    ....
```

You need to either remove that config var (to use assetic for all Bundles) or add the MopaBootstrapBundle

If your are using cssembed, you might notice problems when embedding bootrap via less:

[RuntimeException]
  [ERROR] /path/to/your/bundle/Resources/public/less/../img/glyphicons-halflings.png (No such file or directory)

this is due to cssembed and bootstrap not working so nicely with relative paths.

The most easies ways is to copy the glyphicons-halflings.png to your public img folder

``` bash
cp /your/path/to/bootstrap/img/glyphicons-halflings.png to /path/to/your/bundle/Resources/public/img/
```

so cssembed finds the file in the corresponding position

**IMPORTANT** change the assetic "use_controller" parameter from "true" to "false" in your config_dev.yml



### Configure Routing

Insert the following config into your routing.yml to include the init CMS routes.

	NetworkingInitCmsBundle:
        resource: "@NetworkingInitCmsBundle/Resources/config/routing.yml"
        prefix:   /

### Enable translations

In order for the translations to work it is important to activate the translations in the framework configuration.

Comment in the translator parameter in your config.yml file:

	#config.yml
	framework:
        #esi:             ~
        translator:      { fallback: %locale% }



If they are not already created, you need to add specific folder to allow uploads from users:

.. code-block:: sh

    mkdir web/uploads
    mkdir web/uploads/media
    mkdir web/uploads/images
    chmod -R 0777 web/uploads

3) Install assets
-----------------
```bash
   php app/console mopa:bootstrap:symlink:less
```

4) Create DB schema, insert admin user and insert fixtures
----------------------------------------------------------

**IMPORTANT NOTE** Backup your DB before the next step

There is a command that will set up the DB tables, insert a super admin user and insert some test data to get you going.
This command will however purge the DB when loading the fixtures. You will be prompted to enter a username, email
address and password, these will get you into the backend.

	php app/console networking:cms:install

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
   php app/console mopa:bootstrap:symlink:less
   ```

With these steps taken, bootstrap should be install into vendor/twitter/bootstrap/ and a symlink
been created into vendor/mopa/bootstrap-bundle/Mopa/Bundle/BootstrapBundle/Resources/bootstrap.

6) You have installed the init CMS bundle
-----------------------------------------
Then you can visit your admin dashboard on http://my-server/admin/dashboard

[1]:  http://web.networking.ch
[2]:  https://github.com/networking/init-cms-sandbox/
[3]:  https://github.com/phiamo/MopaBootstrapBundle
[4]:  http://sonata-project.org/bundles/user/master/doc/reference/installation.html#configuration
[5]:  http://sonata-project.org/bundles/media/master/doc/reference/installation.html#configuration