Templating with initcms
=======================

Pretty much all the control is left up to the developer when creating a template. If you know how to use twig, you will
be up and running in no time flat.

There are only two variables that are passed to your twig template from the FrontendPageController:

1. "page": The page entity (as you might of guessed)
2. "admin_pool": The sonata admin bundle Pool object which is used to help display the admin navigation bar when
    an admin user is logged in.


So lets have a look at getting out some of our saved content:

```
   <div class="hero-unit">
        {% for layoutBlock in page.layoutBlock('header') %}
		{{ render_initcms_block('ApplicationNetworkingInitCmsBundle:Content:cms_block.html.twig', layoutBlock)}}
		{% endfor %}
    </div>
```

In this example we loop through the layout blocks for the template zone "header" (which should have been defined in your
template configuration).

We then pass the layoutBlock to our twig extension along with a template of your choice to display the output. It is also
possible to pass an array of parameters. The parameters will passed on to both the template as well as to the content
object.

So what does the twig extension actually do?
-----------------------------------------------

The twig extension extracts the content entity out of the layout block in one of two ways.

 a. If the page is in draft status, a request is made to the DB to fetch the item using the class name and entity id.

 OR

 b. If the page is published the content is serialized and is retrieved using the getSnapshotContent method. The content is
    then deserialized from its json format using the JMS Serializer bundle .

The method "getTemplateOptions($params)" is then called on the content object. The content object delivers back an array
of whatever it is like, and the array plus paramters are passed on the the templating service to be rendered with the
supplied template.

Everything is up to you.

To learn more about content types see [Creating custom content types](content_types.md)

How do I create a navigation?
-----------------------------

###Frontend navigation

The menu system is based on the KnpMenuBundle (see [KnpMenuBundle](https://github.com/KnpLabs/KnpMenuBundle)  on github for more information).

There are currently three methods available in the FrontendMenuBuilder that can be used to create a menu.

1. createMainMenu - which is used to build a menu based on the first level of navigation points
2. creastSubnavMenu - which builds a sub navigation based on the menu given and the current url
3. createFrontendLangMenu - which builds a simple language navigation with the current language active

The navigation setup involves three parts, to be configured in your bundles services.yml (xml) file

Step 1. Setup the Networking FrontendMenuBuilder as your menu factory or extend it:

```
    sandbox_init_cms.menu.frontend_menu_builder:
        class: Networking\InitCmsBundle\Component\Menu\FrontendMenuBuilder
        scope: request
        arguments: [ '@knp_menu.factory', '@security.context', '@service_container' ]
```

Step 2. Configure the Knp menu to use your factory supplying it with the name of the menu and css classes to add the ```<ul>``` element

```
    sandbox_init_cms.menu.frontend_main_menu_left:
        class: Knp\Menu\MenuItem
        factory_service: sandbox_init_cms.menu.frontend_menu_builder
        factory_method: createMainMenu
        arguments: [ '@request', path: "The main menu", classes:  "nav nav-tabs nav-main" ]
        scope: request
        tags:
            - { name: knp_menu.menu, alias: mainMenu }

    sandbox_init_cms.menu.frontend_main_menu_language:
        class: Knp\Menu\MenuItem
        factory_service: sandbox_init_cms.menu.frontend_menu_builder
        factory_method: createFrontendLangMenu
        arguments: [ '@request', %networking_init_cms.page.languages%, "nav nav-pills pull-right" ]
        scope: request
        tags:
            - { name: knp_menu.menu, alias: langMenu }
```

Step 3. (optional) Configure a [mopa bootstrap navbar](https://github.com/phiamo/MopaBootstrapBundle) with template parameters for easy inclusion in the main template

```
    sandbox_init_cms.menu.frontend_top_menu_template:
        class: %mopa_bootstrap.navbar.generic%
        scope: request
        arguments:
            - { pageMenu: @sandbox_init_cms.menu.frontend_main_menu_left=, langMenu: @sandbox_init_cms.menu.frontend_main_menu_language=}
            - {}
            - { logo: "bundles/sandboxinitcms/img/sailing_icon_grey.png", title: "Demo Sailing Club", titleRoute: "networking_init_cms_home", fixedTop: false, isFluid: false, template: SandboxInitCmsBundle:Navbar:navbar.html.twig }
        tags:
            - { name: mopa_bootstrap.navbar, alias: cmsNavbar }

```
Then all you need to do is call ```{{ mopa_bootstrap_navbar('cmsNavbar' ) }}``` in your twig template and Bobs your uncle the
navigation should appear.


### Admin navigation bar

There is an admin toolbar which is included via ajax when an Admin user is logged in,
it can be turned on and off in the configuration of your project

```
    networking_init_cms:
        .....
        admin_toolbar:
            toolbar: true #default is true, will be included
```


