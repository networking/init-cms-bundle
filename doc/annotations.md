Using annotations with sonata
============================



### The Annotations

- Networking\InitCmsBundle\Order on classes for global orders like "show me all properties"
- Networking\InitCmsBundle on properties for specific configurations and excludes if orders are used

> If any FormMapperExclude Annotation is found on a property the reader assumes that there es an Order/FormMapperAll on the class (same goes for the other annotations - List/Form/Datagrid)

Have a look on the Annotations to see what options they accept

### Example

``` php
<?php

namespace YourApp\Entity;

use Networking\InitCmsBundle\Annotation as Sonata;;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @Sonata\Order\ListMapperAll
 * @Sonata\Order\ShowMapperAll
 * @Sonata\Order\FormMapperAll
 *
 * @Sonata\Order\ShowAndFormreorder(with="General", keys={"name"})
 * @Sonata\Order\ShowAndFormreorder(keys={"taxRate"})
 *
 * @Sonata\AutoService()
 */
class Country
{
    /**
     * @var integer $id
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Sonata\ListMapper(identifier=true)
     * @Sonata\Order\FormMapperExclude
     * @Sonata\ShowMapper(with="General")
     */
    protected $id;

    /**
     * @var string $name
     * @ORM\Column(type="string")
     * @Sonata\ShowMapper(with="General")
     */
    protected $name;

    /**
     * @var float
     * @ORM\Column(type="float", name="shipping_free_limit")
     */
    protected $shippingFreeLimit;

    /**
     * @var float
     * @ORM\Column(type="float", name="shipping_fixed_rate")
     */
    protected $shippingFixedRate;

    /**
     * @var float
     * @ORM\Column(type="float", name="tax_rate")
     */
    protected $taxRate;

    /**
     * @ORM\ManyToMany(targetEntity="Article", inversedBy="countries")
     * @ORM\JoinTable(name="article_country")
     * @Sonata\Order\ListMapperExclude
     * @Sonata\FormMapper(options={"required"=false})
     **/
    protected $articles;
}
```

``` php
<?php

namespace YourApp\Entity;

use Networking\InitCmsBundle as Sonata;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

use Application\Sonata\MediaBundle\Entity\Gallery;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @Sonata\Order\ShowAndFormreorder(keys={"name"})
 */
class Article
{
    /**
     * @var string $number
     * @ORM\Column(name="number", type="string", unique=true)
     */
    protected $number;

    /**
     * @var string $name
     * @ORM\Column(type="string")
     * @Sonata\DatagridMapper
     */
    protected $name;

    /**
     * @var string $description
     * @ORM\Column(type="text")
     * @Sonata\Order\ListMapperExclude
     */
    protected $description;

    /**
     * @var string $matchCode
     * @ORM\Column(name="match_code", type="string")
     * @Sonata\Order\ListMapperExclude
     */
    protected $matchCode;

    /**
     * @var string $articleGroup
     * @ORM\Column(name="article_group", type="string")
     * @Sonata\Order\ListMapperExclude
     * @Sonata\Order\ShowMapperExclude
     */
    protected $articleGroup;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ArticlePrice", mappedBy="articleEntity")
     * @Sonata\Order\ListMapperExclude
     */
    protected $prices;

    /**
     * @ORM\ManyToMany(targetEntity="Country", mappedBy="articles")
     **/
    protected $countries;

    /**
     * @var Gallery $pictures
     * @ORM\ManyToOne(
     *      targetEntity="Application\Sonata\MediaBundle\Entity\Gallery",
     *      cascade={"persist"}
     * )
     * @Sonata\FormMapper(
     *      type="sonata_type_model_list",
     *      options={"required"=false},
     *      fieldDescriptionOptions={
     *          "link_parameters"={
     *              "context":"article",
     *              "provider":"sonata.media.provider.image"
     *          }
     *      }
     * )
     */
    protected $pictures;

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @Sonata\FormCallback
     */
    public static function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->add('countries');
    }
}
```

### Using in the Admin (maybe in an AbstractAdmin.php)

#### With the AbstractSonataAdminAnnotationAdmin

```php
<?php

namespace YourApp\Admin;

use Networking\InitCmsBundle\Admin\AbstractSonataAdminAnnotationAdmin;

abstract class AbstractAdmin extends AbstractSonataAdminAnnotationAdmin
{

}
```

#### With Traits (PHP >=5.4)
```php
<?php

namespace YourApp\Admin;

use Networking\InitCmsBundle\Admin\SonataAdminAnnotationAllTrait;

use Sonata\AdminBundle\Admin\Admin;

abstract class AbstractAdmin extends Admin
{
    use SonataAdminAnnotationAllTrait;
}
```

#### Own implementation

``` php
<?php

namespace YourApp\Admin;

use Networking\InitCmsBundle\Reader\SonataAdminAnnotationReaderInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Sonata\AdminBundle\Admin\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

abstract class AbstractAdmin extends Admin
{
    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $this->getSonataAnnotationReader()->configureListFields($this->getClass(), $listMapper);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->getSonataAnnotationReader()->configureFormFields($this->getClass(), $formMapper);
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $this->getSonataAnnotationReader()->configureShowFields($this->getClass(), $showMapper);
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $this->getSonataAnnotationReader()->configureDatagridFilters($this->getClass(), $datagridMapper);
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getConfigurationPool()->getContainer();
    }

    /**
     * @return SonataAdminAnnotationReaderInterface
     */
    protected function getSonataAnnotationReader()
    {
        return $this->getContainer()->get('ibrows_sonataannotation.reader');
    }
}
```