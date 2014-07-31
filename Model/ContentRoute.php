<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Networking\InitCmsBundle\Component\Routing\AbstractRoute;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;
use Symfony\Component\Routing\Route;

/**
 * Networking\InitCmsBundle\Entity\ContentRoute
 *
 *
 * @author net working AG <info@networking.ch>
 */
class ContentRoute extends AbstractRoute implements ContentRouteInterface
{

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $classType
     *
     * @ORM\Column(name="class_type", type="string", length=255)
     */
    protected $classType;

    /**
     * @var integer $id
     *
     * @ORM\Column(name="object_id", type="integer", nullable=true)
     */
    protected $objectId;

    /**
     * $var object $content
     */
    protected $content;

    /**
     * @var string $path
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    protected $path = null;

    /**
     * @var string $controller
     *
     * @ORM\Column(name="controller", type="string", length=255, nullable=true)
     */
    protected $controller;

    /**
     * @var string $template
     *
     * @ORM\Column(name="template", type="string", length=255, nullable=true)
     */
    protected $template;

    /**
     * @var string $templateName
     *
     * @ORM\Column(name="template_name", type="string", length=255, nullable=true)
     */
    protected $templateName;

    /**
     * @var string $locale
     *
     * @ORM\Column(name="locale", type="string", length=255)
     */
    protected $locale;

    /**
     *
     */
    public function __construct()
    {
        $this->menuItem = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->path;
    }

    /**
     * Initializes the object to function as a route
     * @param RouteReferrersReadInterface $content
     * @return $this
     */
    public function setContent(RouteReferrersReadInterface $content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * Handling the missing default 'compiler_class'
     * @see setOptions
     */
    public function getOption($name)
    {
        $option = parent::getOption($name);
        if (null === $option && 'compiler_class' === $name) {
            return 'Symfony\\Component\\Routing\\RouteCompiler';
        }

        return $option;
    }

    /**
     * {@inheritDoc}
     *
     * Handling the missing default 'compiler_class'
     * @see setOptions
     */
    public function getOptions()
    {
        $options = parent::getOptions();
        if (!array_key_exists('compiler_class', $options)) {
            $options['compiler_class'] = 'Symfony\\Component\\Routing\\RouteCompiler';
        }

        return $options;
    }

    /**
     * @param  string $controller
     * @return $this
     */
    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param  int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  string $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param  string $classType
     * @return $this
     */
    public function setClassType($classType)
    {
        $this->classType = $classType;

        return $this;
    }

    /**
     * @return string
     */
    public function getClassType()
    {
        return $this->classType;
    }

    /**
     * @param  int $objectId
     * @return $this
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param  string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $templateName
     */
    public function setTemplateName($templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        $template = new Template(array('template' => $this->getTemplate(), 'vars' => array()));

        return array(
            'route_params' => '',
            '_locale' => $this->getLocale(),
            self::CONTROLLER_NAME => $this->getController(),
            self::TEMPLATE_NAME => $template,
            self::CONTENT_OBJECT => $this->content
        );
    }

    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get the routes that point to this content.
     *
     * @return Route[] Route instances that point to this content
     */
    public function getRoutes()
    {
        // TODO: Implement getRoutes() method.
    }
}
