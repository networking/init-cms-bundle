<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Form\Extension\Field\Type;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FormTypeFieldExtension
 * @package Networking\InitCmsBundle\Form\Extension\Field\Type
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class FormTypeFieldExtension extends AbstractTypeExtension
{
    /**
     * @var array $defaultClasses
     */
    protected $defaultClasses = array();

    /**
     * @param array $defaultClasses
     */
    public function __construct(array $defaultClasses = array())
    {
        $this->defaultClasses = $defaultClasses;
    }


    /**
     * @return array
     */
    public function getDefaultClasses()
    {
        return $this->defaultClasses;
    }

    /**
     * @param array $defaultClasses
     * @return FormTypeFieldExtension
     */
    public function setDefaultClasses(array $defaultClasses)
    {
        $this->defaultClasses = $defaultClasses;

        return $this;
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\FormType';
    }


}