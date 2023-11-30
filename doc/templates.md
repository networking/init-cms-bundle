Templating with initcms
=======================

Pretty much all the control is left up to the developer when creating a template. If you know how to use twig, you will
be up and running in no time flat.

The only thing passed to your template is the page object.

So lets have a look at getting out some of our saved content:

```
   <div class="row">
		<div class="col">
			<div class="jumbotron">
				{% for layoutBlock in page.layoutBlocks('header') %}
					{{ render_layout_block(layoutBlock, 'content/cms_block.html.twig')}}
				{% endfor %}
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="middle">
				{% for layoutBlock in page.layoutBlocks('main_content')  %}
					{{ render_layout_block(layoutBlock, 'content/cms_block.html.twig')}}
				{% endfor %}
			</div>
		</div>
	</div>
```

In this example we loop through the layout blocks for the template zone "header" (which should have been defined in your
template configuration).

We then pass the layoutBlock to our twig extension along with a default fallback template if your content type does not
have a defined template. It is also possible to pass an array of parameters. The parameters will passed on to both the 
template as well as to the content object, so that you can modify the behaviour and rendering of the layout block.


So what does the twig actually do extension do?
-----------------------------------------------

The twig extension is called ```render_layout_block``` and it is defined in the NetworkingInitCmsBundle.

Its job is to get the content object from the layout block and then render it with the template defined in 
the content type configuration, or the template passed as the fallback template parameter.

The method "getTemplateOptions($params)" is then called on the content object. The content object delivers back an array
of whatever it likes, and the array plus paramters are passed on the the templating service to be rendered with the
supplied template. This paramters are then passed on to the template as well as the layout block.

Every thing is up to you.

To learn more about content types see [Creating custom content types](content_types.md)

How to I create a navigation?
-----------------------------

###Frontend navigation

The menu system is based on the KnpMenuBundle (see [KnpMenuBundle](https://github.com/KnpLabs/KnpMenuBundle)  on github for more information).

There are currently three methods available in the FrontendMenuBuilder that can be used to create a menu.

1. createMainMenu - which is used to build a menu based on the first level of navigation points
2. creastSubnavMenu - which builds a sub navigation based on the menu given and the current url
3. createFrontendLangMenu - which builds a simple language navigation with the current language active

The navigation setup involves three parts, to be configured in your bundles services.yaml (xml) file

Step 1. Setup the Networking FrontendMenuBuilder as your menu factory or extend it:

```
    Networking\InitCmsBundle\Menu\FrontendMenuBuilder:
        autowire: true
        arguments:
            $allowLocaleCookie: '%networking_init_cms.allow_locale_cookie%'
```

Step 2. Configure the Knp menu to use your factory supplying it with the name of the menu and css classes to add the ```<ul>``` element

```
services:

    # Create the left main menu and right main menu for the frontend
    app.menu.main:
        class: Knp\Menu\MenuItem
        factory: 'Networking\InitCmsBundle\Menu\FrontendMenuBuilder:createMainMenu'
        arguments: [menu_name: 'Main menu', 'nav nav-tabs' ]
        tags:
            - { name: knp_menu.menu, alias: mainMenu }

    app.menu.lang:
        class: Knp\Menu\MenuItem
        factory: 'Networking\InitCmsBundle\Menu\FrontendMenuBuilder:createFrontendLangMenu'
        arguments: [ '@request_stack', '%networking_init_cms.page.languages%', 'nav nav-pills' ]
        tags:
            - { name: knp_menu.menu, alias: langMenu }
```

Then all you need to do is call ```{{ knp_menu_render(knp_menu_get('mainMenu') }}``` in your twig template and Bobs your uncle the
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


