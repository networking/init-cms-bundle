<?php

declare(strict_types=1);

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
use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;
use Symfony\Component\Routing\Route;

/**
 * Networking\InitCmsBundle\Entity\ContentRoute.
 *
 *
 * @author net working AG <info@networking.ch>
 */
class ContentRoute implements ContentRouteInterface, \Stringable
{
    /**
     * @var int
     *
     */
    protected $id;

    /**
     * @var string
     *
     */
    protected $name;

    /**
     * @var string
     *
     */
    protected $classType;

    /**
     * @var int
     *
     */
    protected $objectId;

    /**
     * $var object $content.
     */
    protected $content;

    /**
     * @var string
     *
     */
    protected $path = null;

    /**
     * @var string
     *
     */
    protected $controller;

    /**
     * @var string
     *
     */
    protected $template;

    /**
     * @var string
     *
     */
    protected $templateName;

    /**
     * @var string
     *
     */
    protected $locale;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Initializes the object to function as a route.
     *
     * @param RouteReferrersReadInterface $content
     *
     * @return $this
     */
    public function setContent(RouteReferrersReadInterface $content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param string $controller
     *
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
     * @param int $id
     *
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
     * @param string $locale
     *
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
     * @param string $classType
     *
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
     * @param int $objectId
     *
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
     *
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
     * @param string $template
     *
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

    public function getContent()
    {
        return $this->content;
    }
}
