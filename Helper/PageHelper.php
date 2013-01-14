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

use Networking\InitCmsBundle\Entity\Page,
    Sonata\AdminBundle\Exception\NoValueException;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageHelper
{
    /**
     * @param $path
     * @return string
     */
    public static function getPageRoutePath($path)
    {
        $pathArray = explode(Page::PATH_SEPARATOR, $path);

        foreach ($pathArray as $key => $path) {
            $pathArray[$key] = preg_replace('/-(\d)+$/', '', $path);
        }
        $path = implode(Page::PATH_SEPARATOR, $pathArray);

        if (substr($path, 0, 1) != Page::PATH_SEPARATOR) {
            $path = Page::PATH_SEPARATOR . $path;
        }

        return $path;
    }

    /**
     * Set the variables to the given content type object
     *
     * @param Page $object
     * @param $fieldName
     * @param $value
     * @param  null                                           $method
     * @return mixed
     * @throws \Sonata\AdminBundle\Exception\NoValueException
     */
    public static function setFieldValue(Page $object, $fieldName, $value, $method = null)
    {
        $setters = array();

        // prefer method name given in the code option
        if ($method) {
            $setters[] = $method;
        }

        $camelizedFieldName = self::camelize($fieldName);
        $setters[] = 'set' . $camelizedFieldName;

        foreach ($setters as $setter) {
            if (method_exists($object, $setter)) {
                call_user_func(array(&$object, $setter), $value);

                return $object;
            }
        }

        if ($object->getId()) {
            throw new NoValueException(sprintf('Unable to set the value of `%s`', $camelizedFieldName));
        }

        return $object;
    }

    /**
     * Fetch the variables from the given content type object
     *
     * @param ContentInterface $object
     * @param $fieldName
     * @param  null                                           $method
     * @return mixed
     * @throws \Sonata\AdminBundle\Exception\NoValueException
     */
    public static function getFieldValue(Page $object, $fieldName, $method = null)
    {

        $getters = array();
        // prefer method name given in the code option
        if ($method) {
            $getters[] = $method;
        }

        $camelizedFieldName = self::camelize($fieldName);
        $getters[] = 'get' . $camelizedFieldName;
        $getters[] = 'is' . $camelizedFieldName;

        foreach ($getters as $getter) {
            if (method_exists($object, $getter)) {
                return call_user_func(array($object, $getter));
            }
        }

        throw new NoValueException(sprintf('Unable to retrieve the value of `%s`', $camelizedFieldName));
    }

    /**
     * Camelize a string
     *
     * @static
     * @param  string $property
     * @return string
     */
    public static function camelize($property)
    {
        return preg_replace(array('/(^|_| )+(.)/e', '/\.(.)/e'), array("strtoupper('\\2')", "'_'.strtoupper('\\1')"), $property);
    }

    /**
     * @param $path
     * @param $id
     * @param $slug
     * @return mixed
     */
    public static function replaceSlugInPath($path, $id, $slug)
    {

        return preg_replace('#(.+/)?.*(-' . $id . '/)#', '$1' . $slug . '$2', $path);
    }
}
