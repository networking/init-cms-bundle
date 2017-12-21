Admin UI
========

The admin modules are based on the [SonataAdminBundle](http://sonata-project.org/bundles/admin/master/doc/index.html). This bundle provides you with a quick and easy way to generate entity lists, filter form, editing/create forms, preview pages, batch actions and much more. To get started with you first admin module, vist [getting started with SonataAdminBundle](http://sonata-project.org/bundles/admin/master/doc/reference/getting_started.html).

We have used this bundle, restyled it a little, and added some small (very small) features to it which you can then use when creating your admin user interface.

Here are a couple of the new features on top of what Sonata already offers.

###NetworkingInitCms BaseAdmin class

We have extended the SonataAdmin Admin class and given it one ore two little things which we often need.

1. The languages of the CMS are available, this is handing when you need to create choice fields which need to list the avaliable languages of the CMS
2. You can get the default language based on the available CMS languages (as per CMS config) and the users language.
3. You can use the iBrows SonataAdminAnnotationBundle to configure your admin bundles if you prefer.

###More Help Texts

The Mopa Bootstrap bundle introduced extra help text areas, unfortunately they are missing from the SonataAdminBundle implementaion, so we have bought them back.

You have 4 choices, which you can be used similtaneously:

1. help_label - this will appear right below the label
2. help_block - this appears below the form widget
3. help - this is the standard help config from sonata and is displayed the same as the help_block, below the form label, and field

```
$formMapper
    ->add(
    'url',
    null,
    array(
        'required' => true,
        'help_label' => 'Enter the URL slug for this page only,
    ),
);
```

###Toggled Filters

We liked the idea of having the filters hidden in a div which can be toggled, but some filter fields just need to be seen, language for example. So we introduced an parameter "hidden" on the filter options (carefull, this is not the field definition options, see [filter field definition](http://sonata-project.org/bundles/doctrine-orm-admin/master/doc/reference/filter_field_definition.html) form more info).

A simple filter field configuration may look like this:

```
protected function configureDatagridFilters(DatagridMapper $datagridMapper)
{
    $datagridMapper
        ->add('name')
        ->add('enabled', null, array('hidden' => true))
        ->add('context', null, array('hidden' => true));
}
```

In the above case, the name field would be shown, and the enabeled and context fields are hidden but can be shown by clicking on the "+ more filter options" link.
