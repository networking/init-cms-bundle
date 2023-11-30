Admin UI
========

The admin modules are based on the [SonataAdminBundle](http://sonata-project.org/bundles/admin/master/doc/index.html). This bundle provides you with a quick and easy way to generate entity lists, filter form, editing/create forms, preview pages, batch actions and much more. To get started with you first admin module, vist [getting started with SonataAdminBundle](http://sonata-project.org/bundles/admin/master/doc/reference/getting_started.html).

We have used this bundle, restyled it a little, and added some small (very small) features to it which you can then use when creating your admin user interface.

Here are a couple of the new features on top of what Sonata already offers.

### NetworkingInitCms BaseAdmin class

We have extended the SonataAdmin Admin class and given it one ore two little things which we often need.

1. The languages of the CMS are available, this is handing when you need to create choice fields which need to list the avaliable languages of the CMS
2. You can get the default language based on the available CMS languages (as per CMS config) and the users language.
3. You can use the Annotations to configure your admin bundles if you prefer.
4. In order for the Datatables to work, we set the default limit of entries to 1000000, if you do not extend the BaseAdmin 
   class, you will need to set this yourself, or risk not all your entries being displayed in the list view.

### More Help Texts

We have added extra field options for added tooltips, popovers, append, and prepend buttons and help blocks.

You have 4 choices, which you can be used similtaneously:

1. help_label - this will appear right below the label
2. help_block - this appears below the form widget
3. help - this is the standard help config from sonata and is displayed the same as the help_block, below the form label, and field
4. help_label_tooltip - this will add a tooltip to the help_label, with an icon
5. help_label_popover - this will add a popover to the help_label, with an icon, like the tooltip, but you need to click on the icon to see the help text
6. help_block_tooltip - this will add a tooltip to the help_block, with an icon
7. help_block_popover - this will add a popover to the help_block, with an icon, like the tooltip, but you need to click on the icon to see the help text
8. widget_addon_prepend - this will add a prepend an icon or text to the form widget, you need to set block_prefix to input_group for this to work.
9. widget_addon_append - this will add a append an icon or text to the form widget, you need to set block_prefix to input_group for this to work.
10. widget_btn_prepend - this will add a prepend a button to the form widget, you need to set block_prefix to input_group for this to work.
11. widget_btn_append - this will add a append a button to the form widget, you need to set block_prefix to input_group for this to work.

```
$formMapper
    ->add(
    'text_block',
    [
        'required' => true,
        'help' => 'text_block.help.text',
        'help_block' => 'text_block.help.text_block',
        'help_label' => 'text_block.help.text_block',
        'help_label_tooltip' => [
            'title' => 'text_block.tooltip.text_label',
            'icon' => 'info-circle',
        ],
        'help_label_popover' => [
            'title' => 'text_block.tooltip.text_block',
            'icon' => 'question-circle',
        ],
        'help_block_tooltip' => [
            'title' => 'text_block.tooltip.text_block',
            'icon' => 'info-circle',
        ],
        'help_block_popover' => [
            'title' => 'text_block.tooltip.text_block',
            'icon' => 'question-circle',
        ],
        'widget_addon_prepend' => [
            'icon' => 'user fs-2',
        ],
        'widget_addon_append' => [
            'text' => '@',
        ],
        'widget_btn_prepend' => [
            'icon' => 'user fs-2',
        ],
        'widget_btn_append' => [
            'text' => '@',
        ],
        'widget_btn_prepend' => [
            'icon' => 'user fs-2',
            'class' => 'btn-light-primary copy-to-clipboard btn-outline',
        ],
        'widget_btn_append' => [
            'icon' => null,
            'label' => 'click me',
            'class' => 'btn-light-primary copy-to-clipboard btn-outline',
        ],
        'block_prefix' => 'input_group',
    ],
);
```

### Toggled Filters

By default, the first filter in the list weill appear first, and the rest will be hidden, but can be shown by clicking on the "More filter options" link.
However, you can change this behaviour by setting the show_filter option to true, on any of the filters you want to show by default.
If a filter is active, it will always be shown.

```
protected function configureDatagridFilters(DatagridMapper $filter): void
{
    $datagridMapper
        ->add('name')
        ->add('enabled', null, ['show_filter' => true])
        ->add('context');
}
```

In the above case, the name and enabeled fields would be shown by default, and the context field is hidden but can be 
shown by clicking on the "+ more filter options" link.
