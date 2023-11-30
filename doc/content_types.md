Content types
=============

A content type is a small snippet or widget that extends the LayoutBlock entity. It is a way of adding content to your 
page.

They can be anything that you like from a simple text block, to a product list. There a just a few things which
you need to know in order to get your content block in the CMS.

1. Your Content type must extend the Networking\InitCmsBundle\Entity\LayoutBlock entity.
2. You need to supply some information about how to display your content in the front and backend of the CMS.

Luckily it is all very simple, there are only three methods to implement so here they are.

#### Field Definition for the content type form ####

We integrated the no longer existing IbrowsSonataAdminAnnotationBundle to help us configure our admin fields for our content type.
Check out [attributes](https://github.com/networking/init-cms-bundle/blob/master/doc/attributes.md) form a more detailed 
information about usage.

There are two ways to define the fields for your content type, either you use the ```#[Networking\InitCmsBundle\Annotation\FormMapper]``` 
attribute in the individual properties of your content type or you use the ```#[Networking\InitCmsBundle\Annotation\FormCallback]``` 
attribute on a method in your content type class, which will be called to configure the form fields for your content type.

##### FormMapper #####
Use the FormMapper attribute to define each field for your content type. 
The FormMapper attribute takes the same parameters as the SonataAdminBundle FormMapper class add() method.
Plus it takes the options "with", "withOptions", "tab" and "ignoreOnParent". With and withOptions are used to group 
fields together in the form. By supplying the same value for the "with" option, the fields will be grouped together in the form.
The "tab" option is used to group fields together in a tab. The "ignoreOnParent" option is used to ignore the field if it is 
called by a parent admin form.

```php
    use Networking\InitCmsBundle\Annotation\FormMapper
    // ..
    #[ORM\Column(type: 'text', nullable: true)]
    #[FormMapper(
        type: \FOS\CKEditorBundle\Form\Type\CKEditorType::class,
        options:
        [
            'required' => true,
        ]
    )]
    protected $text;
```

##### FormCallback #####

Use the FormCallback attribute to define a method which will be called to configure the form fields for your content type.
The FormCallback attribute takes the SonataAdminBundle FormMapper class as a parameter. You can then use the FormMapper 
to configure the fields for your content type.

```php
    use Networking\InitCmsBundle\Annotation\FormCallback
    // ..
    #[ORM\Column(type: 'text', nullable: true)]
    #[FormCallback()]
    protected $text;
    
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->add(
            'text',
            \FOS\CKEditorBundle\Form\Type\CKEditorType::class,
            [
                'required' => true,
            ]
        );
    }
```

It pays to have a look at the [SonataAdminBundle](http://sonata-project.org/bundles/admin/master/doc/index.html) and [SonataDoctrineORMBundle](http://sonata-project.org/bundles/doctrine-orm-admin/master/doc/index.html) for more information on field configuration.

----------------------------------------------------
#### Display the content in the frontend ####
```getTemplateOptions``` method is used by the twig extension to help it render the content in the frontend. 
You are free to return what ever you like, so long as it is an array. The parameters passed to the method are the parameters
passed to the twig extension, and you can use them to modify the behaviour of your content type, such as limiting the number
of items to display in a list, or adding extra css classes to the rendered content.

```
public function getTemplateOptions($params = []): array
{
    return [];
}
```

----------------------------------------------------
#### Display the content in the backend ####
```getAdminContent``` this method is very similar to the the get TemplateOptions method, but it is used to render the 
content in the backend. You can use it to add extra information to the content type, such as a preview image or a summary
or a template to be used to render the content in the backend.
If the template option is not set, the default template is used to render the content in the backend.
If the content parameter is set, it will be passed to the template along with the layout block.
Additionally the paramter "is_admin" is passed to the template, which can be used to modify the behaviour of the template.

```
public function getAdminContent(): array
{
    return [
        'content' => ['text' => $this->getText()]
        'template' => '@NetworkingInitCmsAdmin/Content/preview_text.html.twig',
        ];
}
```


------------------------------------
#### Serializing for versioning ####

We use the Symfony serializer to serialize the content type for versioning. 
Pleaes see the [Symfony Serializer](https://symfony.com/doc/current/components/serializer.html) for more information.



------------------------------------    
#### Page copying and translations ####   
 
There is the possibility to copy page singularily, which is done in the translation panel of the page editing mask, or batch translation from one language to another via the route /admin/pages/batch_translation.
The batch function is at the moment only available to the super admin users, and will try to copy the whole tree, skipping pages that have already been translated.

If you want to have your content be able to be copied correctly to another language it is important that you add a clone method, where the id is set to null and you tack care of any entity relationships.