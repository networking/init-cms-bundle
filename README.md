Installation of the Networking symfony CMS
====================================

This is the initial guide to the standard installation of the
symfony based frame for our new CMS


Installing the Standard Edition
----------------------------------

### Get the base project from gitolite
First git the base version from the gitolite repository
```
git clone gitolite@net07.networking.ch:symfony_cms
cd symfony_cms
``

Create a parameters file
```
cp app/config/parameters.yml.default app/config/parameters.yml
``

### Use Composer (*recommended*)
Next download the composer.phar file right for your setup

```
curl -s http://getcomposer.org/installer | php
```

Now we can install the vendor library with the help of composer.

```
php composer.phar install
```

Create folder /uploads/media in the web root directory and make it RW+

```
cd web
mkdir uploads
mkdir uploads/media
chmod -R 777 uploads
``

### Configure your CMS
It is time to configure your cms, navigate to the location of your config.php file

http://localhost/config.php

Or edit the parameters.yml file directly

Now run the following command in the command line tool, you will be asked to enter an administration user, please
remember your password.

```
php app/console networking:cms:install
```