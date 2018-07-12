Content types
=============

A content type is a small snippet or widget that can be placed within any layout block in your template.
They can be anything that you like from a simple text block, to a product list. There a just a few things which
you need to know in order to get your content block in the CMS.

1. Your Content type must implement the Networking\InitCmsBundle\Entity\ContentInterface
2. You must supply a serializer configuration (annotation, yml etc) so that your content can be serialized and deserialized when a page is published.
3. You need to supply some information about how to display your content in the front and backend of the CMS.

Luckily it is all very simple, there are only three methods to implement so here they are.

#### Field Definition for the content type form ####

We use the [IbrowsSonataAdminAnnotationBundle](https://github.com/ibrows/IbrowsSonataAdminAnnotationBundle) to help us configure our admin fields for our content type.

Here is an example from the Text entity

```
/**
 * @var text $content
 * @ORM\Column(name="text", type="text", nullable=true)
 *  @Sonata\FormMapper(
 *      name="text",
 *      type="ckeditor",
 *      options={
 *          "label_render" = false,
 *          "horizontal_input_wrapper_class" = "col-md-12",
 *          "horizontal_label_offset_class" = "",
 *          "label" = false,
 *          "required"=false
 *      }
 * )
 */
protected $text;
```

It pays to have a look at the [SonataAdminBundle](http://sonata-project.org/bundles/admin/master/doc/index.html) and [SonataDoctrineORMBundle](http://sonata-project.org/bundles/doctrine-orm-admin/master/doc/index.html) for more information on field configuration.

----------------------------------------------------
#### Display the content in the frontend ####
```getTemplateOptions``` method is used by the twig extension to help it render the content in the frontend. You are free to return what ever you like, so long as it is an array. How you choose to display the content in the template is up to you.

```
public function getTemplateOptions($params = array())
{
    return array('text' => $this->getText());
}
```

----------------------------------------------------
#### Display the content in the backend ####
```getAdminContent``` this method is very similar to the the get TemplateOptions method except that it takes no parameters and has to return an array consiting of two keys.
The first key is ***content*** which can contain what ever you like (honestly). The second key is ***template*** which must return a template which will be used to render the content type in the CMS admin area.

```
public function getAdminContent()
{
    $mediaItems = $this->getMediaGallery() ? $this->getMediaGallery()->getGalleryHasMedias() : array();

    return array(
        'content' => array('mediaItems' => $mediaItems),
        'template' => '@NetworkingInitCms/GalleryAdmin/gallery_view_block.html.twig'
    );
}
```


------------------------------------
#### Serializing for versioning ####

The last very important part is the serializing mechanism. This can be a bit tricky depending on how complex your content type is. The more DB associations that an entity has the more mapping you will need to do.

You will first need to create a folder in your **Resources/config** folder called **Serializer** and then create a config file with the name **Enity.YourContentName.yml**, or you can use annotations directly in the Entity class.

Here is a simple example of serializer configuration for the Text content type:

```
Networking\InitCmsBundle\Entity\Text:
    properties:
        id:
            type: integer
        layoutBlock:
            exclude: true
        text:
            type: string
        createdAt:
            type: DateTime
        updatedAt:
            type: DateTime
```

For a more detailed look at serialization have a look at the [JMSSerializerBundle documentation](http://jmsyst.com/bundles/JMSSerializerBundle)


------------------------------------    
#### Page copying and translations ####   
 
There is the possibility to copy page singularily, which is done in the translation panel of the page editing mask, or batch translation from one language to another via the route /admin/pages/batch_translation.
The batch function is at the moment only available to the super admin users, and will try to copy the whole tree, skipping pages that have already been translated.

If you want to have your content be able to be copied correctly to another language it is important that you add a clone method, where the id is set to null and you tack care of any entity relationships.