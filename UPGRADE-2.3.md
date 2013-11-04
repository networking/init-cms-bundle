Upgrade
=======
### General

It is now imperative to extend the Page and User classes. You can either use the following command to create a
bundle in your src folder which extends the NetworkingInitCmsBundle.

```
    php app/console networking:init_cms:generate
```

Or you can manually extend the bundle and add
the necessary configuration for doctrine (see the Page.orm.xml.skeleton and User.orm.xml.skeleton files in the
Resources/config/doctrine folder).

### Configuration

There are two new parameters which point to your Page and User Objects. Please change these to reflect your Page and User
Classes should they be located in a different location.

networking_init_cms:
    class:
        page: "Application\\Networking\\InitCmsBundle\\Entity\\Page" # Default value
        user: "Application\\Networking\\InitCmsBundle\\Entity\\User" # Default value

All the services are now defined in separate xml files, all the models are defined in *.orm.yml files


### Preparation for alternative db drivers

All the entities have been abstracted to the Model folder and an ORM version is available in the Doctrine folder. It is
theoretically possible to create a folder called Document where you could have your mongodb entities for example.
We will work on this in the future, but it is not a priority for us right at the moment.

### Translation Admin

New is the translation admin module courtesy of the nice guys at ibrows. Please read the [documentation][1] provided on
github on how to load translations and configure the bundle

[1]: https://github.com/ibrows/IbrowsSonataTranslationBundle

