<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Component\Routing;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Route as SymfonyRoute;

/**
 * Dummy Route Class for creating routes on the run. Used when building navigation items.
 *
 * Class CMSRoute
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class Route extends SymfonyRoute implements RouteObjectInterface
{
    const LOCALE = '_locale';

    /**
     * @var array
     */
    private $defaults = [];

    /**
     * @var
     */
    protected $content;

    /**
     * @param $pattern
     * @param array $defaults
     */
    public function __construct($pattern, array $defaults = [])
    {
        $this->setPath($pattern);
        $this->setDefaults($defaults);
    }

    /**
     * @return mixed
     */
    public function getRouteKey(): ?string
    {
        return $this->getPath();
    }

    /**
     * Sets the defaults.
     *
     * This method implements a fluent interface.
     *
     * @param array $defaults The defaults
     *
     * @return Route The current Route instance
     */
    public function setDefaults(array $defaults): static
    {
        $this->defaults = [];

        return $this->addDefaults($defaults);
    }

    /**
     * Adds defaults.
     *
     * This method implements a fluent interface.
     *
     * @param array $defaults The defaults
     *
     * @return Route The current Route instance
     */
    public function addDefaults(array $defaults): static
    {
        foreach ($defaults as $name => $default) {
            $this->defaults[$name] = $default;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * Handling the missing default 'compiler_class'
     *
     * @see setOptions
     */
    public function getOption($name): mixed
    {
        $option = parent::getOption($name);
        if (null === $option && 'compiler_class' === $name) {
            return 'Symfony\\Component\\Routing\\RouteCompiler';
        }

        return $option;
    }

    /**
     * {@inheritdoc}
     *
     * Handling the missing default 'compiler_class'
     *
     * @see setOptions
     */
    public function getOptions(): array
    {
        $options = parent::getOptions();
        if (!array_key_exists('compiler_class', $options)) {
            $options['compiler_class'] = 'Symfony\\Component\\Routing\\RouteCompiler';
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * Get the content document this route entry stands for. If non-null,
     * the ControllerClassMapper uses it to identify a controller and
     * the content is passed to the controller.
     *
     * If there is no specific content for this url (i.e. its an "application"
     * page), may return null.
     *
     * @return object the document or entity this route entry points to
     */
    public function getContent(): ?object
    {
        return $this->getDefault(self::CONTENT_OBJECT);
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->getDefault(self::LOCALE);
    }
}
