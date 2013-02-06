<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Networking\InitCmsBundle\Component\Routing\AbstractRoute;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Cmf\Component\Routing\RouteAwareInterface;

/**
 * Networking\InitCmsBundle\Entity\ContentRoute
 *
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="content_route", uniqueConstraints={
 * @ORM\UniqueConstraint(name="path_idx", columns={"path", "locale", "class_type"})
 * })
 * @ORM\Entity(repositoryClass="Networking\InitCmsBundle\Entity\ContentRouteRepository")
 *
 * @author net working AG <info@networking.ch>
 */
class ContentRoute extends AbstractRoute
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
    protected $path;

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

    public function __toString()
    {
        return $this->path;
    }

    /**
     * Initializes the object to function as a route
     * @param \Symfony\Cmf\Component\Routing\RouteAwareInterface $content
     * @return ContentRoute
     */
    public function initializeRoute(RouteAwareInterface $content)
    {
        $this->content = $content;
        $this->setPattern($this->getPath());
        $this->setOptions(array('compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler'));

        if (method_exists($content, 'getLocale')) {
            $this->setDefault('_locale', $content->getLocale());
        }

        return $this;
    }

    /**
     * @param  string       $controller
     * @return ContentRoute
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
     * @param  int          $id
     * @return ContentRoute
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
     * @param  string       $locale
     * @return ContentRoute
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
     * @param  string       $classType
     * @return ContentRoute
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
     * @param  int          $objectId
     * @return ContentRoute
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
     * @return ContentRoute
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
     * @param  string       $template
     * @return ContentRoute
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
}
