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

use Networking\InitCmsBundle\Model\PageSnapshotInterface;
use Networking\InitCmsBundle\Serializer\PageSnapshotDeserializationContext;
use Sonata\AdminBundle\Exception\NoValueException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PageHelper
 * @package Networking\InitCmsBundle\Helper
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageHelper
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @param $path
     * @return string
     */
    public static function getPageRoutePath($path)
    {
        $pathArray = explode(PageInterface::PATH_SEPARATOR, $path);

        foreach ($pathArray as $key => $path) {
            $pathArray[$key] = preg_replace('/-(\d)+$/', '', $path);
        }
        $path = implode(PageInterface::PATH_SEPARATOR, $pathArray);

        //add first slash
        if (substr($path, 0, 1) != PageInterface::PATH_SEPARATOR) {
            $path = PageInterface::PATH_SEPARATOR . $path;
        }

        //remove last slash
//        if(substr($path, -1, 1) == PageInterface::PATH_SEPARATOR){
//            $path = substr($path,0, -1);
//        }

        return $path;
    }

    /**
     * Set the variables to the given content type object
     *
     * @param PageInterface $object
     * @param $fieldName
     * @param $value
     * @param  null $method
     * @return mixed
     * @throws \Sonata\AdminBundle\Exception\NoValueException
     */
    public static function setFieldValue(PageInterface $object, $fieldName, $value, $method = null)
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
     * @param PageInterface $object
     * @param $fieldName
     * @param  null $method
     * @return mixed
     * @throws \Sonata\AdminBundle\Exception\NoValueException
     */
    public static function getFieldValue(PageInterface $object, $fieldName, $method = null)
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
        return ContentInterfaceHelper::camelize($property);
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
     * @param $id
     * @return object
     */
    public function getService($id)
    {
        return $this->container->get($id);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getParameter($id)
    {
        return $this->container->getParameter($id);
    }

    /**
     * Create a snapshot of a given page.
     *
     * @param PageInterface $page
     */
    public function makePageSnapshot(PageInterface $page)
    {
        /** @var \JMS\Serializer\SerializerInterface $serializer */
        $serializer = $this->getService('serializer');


        if ($this->getParameter('networking_init_cms.db_driver') == 'orm') {
            /** @var \Doctrine\Common\Persistence\ObjectManager $em */
            $em = $this->getService('doctrine')->getManager();
        } else {
            /** @var \Doctrine\Common\Persistence\ObjectManager $manager */
            $em = $this->getService('doctrine_mongodb')->getManager();
        }

        foreach ($page->getLayoutBlock() as $layoutBlock) {

            /** @var \Networking\InitCmsBundle\Model\layoutBlockInterface $layoutBlock */
            $layoutBlockContent = $em->getRepository($layoutBlock->getClassType())->find(
                $layoutBlock->getObjectId()
            );
            $layoutBlock->takeSnapshot($serializer->serialize($layoutBlockContent, 'json'));
        }
        /**  @var  \Networking\InitCmsBundle\Model\PageSnapshotManagerInterface $pageSnapshotManager */
        $pageSnapshotManager = $this->getService('networking_init_cms.page_snapshot_manager');
        $pageSnapshotClass = $pageSnapshotManager->getClassName();

        /** @var  \Networking\InitCmsBundle\Model\PageSnapshotInterface $pageSnapshot */
        $pageSnapshot = new $pageSnapshotClass($page);
        $pageSnapshot->setVersionedData($serializer->serialize($page, 'json'))
            ->setPage($page);

        if ($oldPageSnapshot = $page->getSnapshot()) {
            $snapshotContentRoute = $oldPageSnapshot->getContentRoute();
        } else {


            $contentRouteManager = $this->getService('networking_init_cms.content_route_manager');
            $contentRouteClass = $contentRouteManager->getClass();

            /** @var  \Networking\InitCmsBundle\Model\ContentRouteInterface $snapshotContentRoute */
            $snapshotContentRoute = new $contentRouteClass();
        }

        $pageSnapshot->setContentRoute($snapshotContentRoute);
        $pageSnapshot->setPath(self::getPageRoutePath($page->getPath()));



        $em->persist($pageSnapshot);
        $em->flush();

        $snapshotContentRoute->setPath(self::getPageRoutePath($page->getPath()));
        $snapshotContentRoute->setObjectId($pageSnapshot->getId());


        if($oldPageSnapshot && ($oldPageSnapshot->getPath() != self::getPageRoutePath($page->getPath()))){
            /** @var \Networking\InitCmsBundle\Lib\PhpCacheInterface $phpCache */
            $phpCache = $this->getService('networking_init_cms.lib.php_cache');
            $phpCache->clean();
        }


        $em->persist($snapshotContentRoute);
        $em->flush();
    }

    /**
     * Unserialize the PageSnapshot data into a page object
     *
     * @param PageSnapshotInterface $pageSnapshot
     * @param bool $unserializeTranslations
     * @return PageInterface
     */
    public function unserializePageSnapshotData(PageSnapshotInterface $pageSnapshot, $unserializeTranslations = true)
    {
        /** @var \JMS\Serializer\SerializerInterface $serializer */
        $serializer = $this->getService('serializer');
        $context = new PageSnapshotDeserializationContext();
        $context->setDeserializeTranslations($unserializeTranslations);

        return $serializer->deserialize($pageSnapshot->getVersionedData(), $pageSnapshot->getResourceName(), 'json', $context);
    }

    /**
     * create a copy of a given page object in a given locale
     *
     * @param $page
     * @param $locale
     * @return \Networking\InitCmsBundle\Model\Page
     */
    public function makeTranslationCopy(PageInterface $page, $locale)
    {
        if ($this->getParameter('networking_init_cms.db_driver') == 'orm') {
            /** @var \Doctrine\Common\Persistence\ObjectManager $em */
            $em = $this->getService('doctrine')->getManager();
        } else {
            /** @var \Doctrine\Common\Persistence\ObjectManager $em */
            $em = $this->getService('doctrine_mongodb')->getManager();
        }

        /** @var \Networking\InitCmsBundle\Model\PageManagerInterface $pageManger */
        $pageManger = $this->getService('networking_init_cms.page_manager');


        $pageClass = $pageManger->getClassName();
        /** @var PageInterface $pageCopy */
        $pageCopy = new $pageClass;

        $pageCopy->setPageName($page->getPageName());
        $pageCopy->setMetaTitle($page->getMetaTitle());
        $pageCopy->setUrl($page->getUrl());
        $pageCopy->setMetaKeyword($page->getMetaKeyword());
        $pageCopy->setMetaDescription($page->getMetaDescription());
        $pageCopy->setActiveFrom($page->getActiveFrom());
        $pageCopy->setIsHome($page->getIsHome());
        $pageCopy->setLocale($locale);
        $pageCopy->setTemplateName($page->getTemplateName());
        $pageCopy->setOriginal($page);

        $layoutBlocks = $page->getLayoutBlock();

        foreach ($layoutBlocks as $layoutBlock) {

            /** @var $newLayoutBlock \Networking\InitCmsBundle\Model\LayoutBlockInterface */
            $newLayoutBlock = clone $layoutBlock;

            $content = $em->getRepository($newLayoutBlock->getClassType())->find(
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

    /**
     * Fills the request object with the content route parameters if found
     *
     * @param Request $request
     * @return Request
     */
    public function matchContentRouteRequest(Request $request)
    {
        /** @var \Symfony\Cmf\Component\Routing\DynamicRouter $dynamicRouter */
        $dynamicRouter = $this->container->get('networking_init_cms.cms_router');
        $requestParams = $dynamicRouter->matchRequest($request);

        if (is_array($requestParams) && !empty($requestParams)) {
            $request->attributes->add($requestParams);

            unset($requestParams['_route']);
            unset($requestParams['_controller']);
            $request->attributes->set('_route_params', $requestParams);

            $configuration = $request->attributes->get('_template');
            $request->attributes->set('_template', $configuration->getTemplate());
            $request->attributes->set('_template_vars', $configuration->getVars());
            $request->attributes->set('_template_streamable', $configuration->isStreamable());
        }

        return $request;
    }

    /**
     * Returns if a page is active or inactive based on json string from page snapshot
     *
     * @param $jsonString
     * @return bool
     */
    public function jsonPageIsActive($jsonString)
    {
        $page = json_decode($jsonString, true);

        $now = new \DateTime();

        $activeStart = array_key_exists('active_from', $page) ? new \DateTime($page['active_from']) : new \DateTime;
        $activeEnd = array_key_exists('active_to', $page) ? new \DateTime($page['active_to']) : new \DateTime;

        if ($now->getTimestamp() >= $activeStart->getTimestamp() &&
            $now->getTimestamp() <= $activeEnd->getTimestamp()
        ) {
            return ($page['status'] == PageInterface::STATUS_PUBLISHED);
        }

        return false;
    }
}
