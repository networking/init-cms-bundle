<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Helper;

use Networking\InitCmsBundle\Model\ContentInterface;
use Sonata\AdminBundle\Exception\NoValueException;

/**
 * Class ContentInterfaceHelper.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class ContentInterfaceHelper
{
    /**
     * Set the variables to the given content type object.
     *
     * @param ContentInterface $object
     * @param $fieldName
     * @param $value
     * @param null $method
     *
     * @return mixed
     *
     * @throws \Sonata\AdminBundle\Exception\NoValueException
     */
    public function setFieldValue(ContentInterface $object, $fieldName, $value, $method = null)
    {
        $setters = [];

        // prefer method name given in the code option
        if ($method) {
            $setters[] = $method;
        }

        $camelizedFieldName = self::camelize($fieldName);
        $setters[] = 'set'.$camelizedFieldName;

        foreach ($setters as $setter) {
            if (method_exists($object, $setter)) {
                call_user_func([&$object, $setter], $value);

                return $object;
            }
        }

        if ($object->getId()) {
            throw new NoValueException(sprintf('Unable to set the value of `%s`', $camelizedFieldName));
        }

        return $object;
    }

    /**
     * Fetch the variables from the given content type object.
     *
     * @param ContentInterface $object
     * @param $fieldName
     * @param null $method
     *
     * @return mixed
     *
     * @throws \Sonata\AdminBundle\Exception\NoValueException
     */
    public function getFieldValue(ContentInterface $object, $fieldName, $method = null)
    {
        $getters = [];
        // prefer method name given in the code option
        if ($method) {
            $getters[] = $method;
        }

        $camelizedFieldName = self::camelize($fieldName);
        $getters[] = 'get'.$camelizedFieldName;
        $getters[] = 'is'.$camelizedFieldName;

        foreach ($getters as $getter) {
            if (method_exists($object, $getter)) {
                return call_user_func([$object, $getter]);
            }
        }

        throw new NoValueException(sprintf('Unable to retrieve the value of `%s`', $camelizedFieldName));
    }

    /**
     * Camelize a string.
     *
     * @static
     *
     * @param string $property
     *
     * @return string
     */
    public static function camelize($property)
    {
        $callback = function ($matches) {
            if ($matches[1] === '.') {
                return '_'.strtoupper($matches[2]);
            }

            return strtoupper($matches[2]);
        };

        return ucfirst(preg_replace_callback('/([_\ .])(.)/', $callback, $property));
    }
}
