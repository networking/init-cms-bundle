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
namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Networking\InitCmsBundle\Model\ContentRouteInterface;
use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;
use Symfony\Component\Serializer\Attribute\Ignore;

/**
 * Networking\InitCmsBundle\Entity\ContentRoute.
 *
 * @author net working AG <info@networking.ch>
 */
#[ORM\Entity]
#[ORM\Table(name: 'content_route')]
#[ORM\UniqueConstraint(name: 'content_route_idx', columns: ['path', 'locale', 'class_type'])]
class ContentRoute implements ContentRouteInterface, \Stringable
{
    /**
     * @var int
     *
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    /**
     * @var string
     *
     */
    #[ORM\Column(type: 'string', length: 255)]
    protected $name;

    /**
     * @var string
     *
     */
    #[ORM\Column(type: 'string', length: 255)]
    protected $classType;

    /**
     * @var int
     *
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    protected $objectId;

    /**
     * @var string
     *
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $path = null;

    /**
     * @var string
     *
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $controller;

    /**
     * @var string
     *
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $template;

    /**
     * @var string
     *
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $templateName;

    /**
     * @var string
     *
     */
    #[ORM\Column(type: 'string', length: 6)]
    protected $locale;

    /**
     * $var object $content.
     */
    #[Ignore]
    protected $content;

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
