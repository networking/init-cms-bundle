<?php
/*
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Helper;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class BundleGuesser
{

    /**
     * @var \ReflectionClass
     */
    protected $reflector;

    /**
     * @param $class
     * @return BundleGuesser
     */
    public function initialize($class)
    {

        $this->reflector = new \ReflectionClass(get_class($class));
        return $this;

    }

    /**
     *
     * @return string
     */
    public function getBundleName()
    {
        return ($p1 = strpos($ns = $this->getNamespace(), '\\')) === false ? $ns :
            substr($ns, 0, ($p2 = strpos($ns, '\\', $p1 + 1)) === false ? strlen($ns) : $p2);
    }

    /**
     *
     * @return string
     */
    public function getBundleShortName()
    {
        return str_replace('\\', '', $this->getBundleName());
    }

    /**
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->reflector->getNamespaceName();
    }

    /**
     *
     * @return string
     */
    public function getShortName()
    {
        return $this->reflector->getShortName();
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->reflector->getName();
    }

    /**
     *
     * @return string
     */
    public function guessEntityNamespace()
    {
        return ($pos = strrpos($ns = $this->getNamespace(), '\\')) === false ? $ns
            : sprintf("%s\%s", substr($ns, 0, $pos), 'Entity');
    }

    /**
     *
     * @return string
     */
    public function guessEntityShortName()
    {
        return ($pos = strpos($short = $this->getShortName(), 'Controller')) === false ? $short :
            substr($short, 0, $pos);
    }

    /**
     *
     * @return string
     */
    public function guessEntityName()
    {
        return sprintf('%s\\%s', $this->guessEntityNamespace(), $this->guessEntityShortName());
    }

    /**
     *
     * @return string
     */
    public function guessRepositoryNamespace()
    {
        return ($p = strrpos($ns = $this->getNamespace(), '\\')) === false ? $ns
            : sprintf("%s\Repository", substr($ns, 0, $p));
    }

    /**
     *
     * @return string
     */
    public function guessRepositoryShortName()
    {
        return sprintf(
            '%sRepository',
            ($pos = strpos($s = $this->getShortName(), 'Controller')) === false ? $s : substr($s, 0, $pos)
        );
    }

    /**
     *
     * @return string
     */
    public function guessRepositoryName()
    {
        return sprintf('%s\\%s', $this->guessRepositoryNamespace(), $this->guessRepositoryShortName());
    }

    /**
     *
     * @return string
     */
    public function guessFormTypeNamespace()
    {
        return ($p = strrpos($ns = $this->getNamespace(), '\\')) === false ? $ns
            : sprintf("%s\Form\Type", substr($ns, 0, $p));
    }

    /**
     *
     * @return string
     */
    public function guessFormTypeShortName()
    {
        return sprintf(
            '%sFormType',
            ($pos = strpos($s = $this->getShortName(), 'Controller')) === false ? $s : substr($s, 0, $pos)
        );
    }

    /**
     *
     * @return string
     */
    public function guessFormTypeName()
    {
        return sprintf('%s\%s', $this->guessFormTypeNamespace(), $this->guessFormTypeShortName());
    }

}
