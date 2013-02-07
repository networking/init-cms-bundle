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

use Networking\InitCmsBundle\Entity\Page,
    Sonata\AdminBundle\Exception\NoValueException,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Doctrine\ORM\EntityManager,
    Networking\InitCmsBundle\Entity\PageSnapshot,
    Networking\InitCmsBundle\Entity\ContentRoute;

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
        return preg_replace(
            array('/(^|_| )+(.)/e', '/\.(.)/e'),
            array("strtoupper('\\2')", "'_'.strtoupper('\\1')"),
            $property
        );
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

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Create a snapshot of a given page.
     *
     * @param Page $page
     */
    public function makePageSnapshot(Page $page)
    {
        $serializer = $this->container->get('serializer');
        $manager = $this->container->get('doctrine')->getManager();

        foreach ($page->getLayoutBlock() as $layoutBlock) {
            /** @var $layoutBlock \Networking\InitCmsBundle\Entity\LayoutBlock */
            $layoutBlockContent = $manager->getRepository($layoutBlock->getClassType())->find(
                $layoutBlock->getObjectId()
            );
            $layoutBlock->takeSnapshot($serializer->serialize($layoutBlockContent, 'json'));
        }
        /** @var $pageSnapshot \Networking\InitCmsBundle\Entity\PageSnapshot */
        $pageSnapshot = new PageSnapshot($page);

        $pageSnapshot->setVersionedData($serializer->serialize($page, 'json'))
            ->setPage($page);

        if ($oldPageSnapshot = $page->getSnapshot()) {
            $snapshotContentRoute = $oldPageSnapshot->getContentRoute();
        } else {
            /** @var $snapshotContentRoute \Networking\InitCmsBundle\Entity\ContentRoute */
            $snapshotContentRoute = new ContentRoute();
        }

        $pageSnapshot->setContentRoute($snapshotContentRoute);

        $manager->persist($pageSnapshot);
        $manager->flush();

        $snapshotContentRoute->setPath(self::getPageRoutePath($page->getPath()));
        $snapshotContentRoute->setObjectId($pageSnapshot->getId());

        $manager->persist($snapshotContentRoute);
        $manager->flush();
    }

    /**
     * create a copy of a given page object in a given locale
     *
     * @param $page
     * @param $locale
     * @return \Networking\InitCmsBundle\Entity\Page
     */
    public function makeTranslationCopy(Page $page, $locale)
    {
        $doctrine = $this->container->get('doctrine');
        $em = $doctrine->getManager();

        $pageCopy = new Page();

        $pageCopy->setWorkingTitle($page->getWorkingTitle());
        $pageCopy->setMetaTitle($page->getMetaTitle());
        $pageCopy->setUrl($page->getUrl());
        $pageCopy->setMetaKeyword($page->getMetaKeyword());
        $pageCopy->setMetaDescription($page->getMetaDescription());
        $pageCopy->setActiveFrom($page->getActiveFrom());
        $pageCopy->setIsHome($page->getIsHome());
        $pageCopy->setLocale($locale);
        $pageCopy->setTemplate($page->getTemplate());
        $pageCopy->setOriginal($page);

        $layoutBlocks = $page->getLayoutBlock();

        foreach ($layoutBlocks as $layoutBlock) {

            /** @var $newLayoutBlock LayoutBlock */
            $newLayoutBlock = clone $layoutBlock;

            $content = $doctrine->getRepository($newLayoutBlock->getClassType())->find(
                $newLayoutBlock->getObjectId()
            );
            $newContent = clone $content;

            $em->persist($newContent);
            $em->flush();

            $newLayoutBlock->setObjectId($newContent->getId());
            $newLayoutBlock->setPage($pageCopy);
            $em->persist($newLayoutBlock);
        }

        $em->persist($pageCopy);
        $em->flush();

        return $pageCopy;

    }
}
