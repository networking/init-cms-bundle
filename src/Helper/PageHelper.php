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
namespace Networking\InitCmsBundle\Helper;

//use JMS\Serializer\SerializerInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Networking\InitCmsBundle\Model\ContentRouteManagerInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Networking\InitCmsBundle\Model\PageSnapshotInterface;
use Networking\InitCmsBundle\Model\PageSnapshotManagerInterface;
use Networking\InitCmsBundle\Serializer\LayoutBlockNormalizer;
use Networking\InitCmsBundle\Serializer\PageSnapshotDeserializationContext;
use Sonata\AdminBundle\Exception\NoValueException;
use Networking\InitCmsBundle\Model\PageInterface;
use Symfony\Cmf\Component\Routing\DynamicRouter;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class PageHelper.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageHelper
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface|object
     */
    protected $objectManager;

    /**
     * @var PageManagerInterface
     */
    protected $pageManager;

    /**
     * @var PageSnapshotManagerInterface
     */
    protected $pageSnapshotManager;

    /**
     * @var ContentRouteManagerInterface
     */
    protected $contentRouteManager;

    /**
     * @var DynamicRouter
     */
    protected $router;

    /**
     * @var PageCacheInterface
     */
    protected $pageCache;

    /**
     * PageHelper constructor.
     *
     * @param bool                         $allowLocaleCookie
     * @param bool                         $singleLanguage
     */
    public function __construct(
        SerializerInterface $serializer,
        ManagerRegistry $registry,
        PageManagerInterface $pageManager,
        PageSnapshotManagerInterface $pageSnapshotManager,
        ContentRouteManagerInterface $contentRouteManager,
        DynamicRouter $router,
        PageCacheInterface $pageCache,
        protected $allowLocaleCookie = true,
        protected $singleLanguage = false
    ) {
        $this->serializer = $serializer;
        $this->registry = $registry;
        $this->objectManager = $registry->getManager();
        $this->pageManager = $pageManager;
        $this->pageSnapshotManager = $pageSnapshotManager;
        $this->contentRouteManager = $contentRouteManager;
        $this->router = $router;
        $this->pageCache = $pageCache;
    }

    /**
     * @param $path
     *
     * @return string
     */
    public static function getPageRoutePath($path)
    {
        $pathArray = explode(PageInterface::PATH_SEPARATOR, (string) $path);

        foreach ($pathArray as $key => $path) {
            $pathArray[$key] = preg_replace('/-(\d)+$/', '', $path);
        }
        $path = implode(PageInterface::PATH_SEPARATOR, $pathArray);

        //add first slash
        if (substr($path, 0, 1) != PageInterface::PATH_SEPARATOR) {
            $path = PageInterface::PATH_SEPARATOR.$path;
        }

        return $path;
    }

    /**
     * Set the variables to the given content type object.
     *
     * @param $fieldName
     * @param $value
     * @param null $method
     *
     * @return mixed
     * @throws \Sonata\AdminBundle\Exception\NoValueException
     */
    public static function setFieldValue(PageInterface $object, $fieldName, $value, $method = null)
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
     * @param $fieldName
     * @param null $method
     *
     * @return mixed
     * @throws \Sonata\AdminBundle\Exception\NoValueException
     */
    public static function getFieldValue(PageInterface $object, $fieldName, $method = null)
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
        return ContentInterfaceHelper::camelize($property);
    }

    /**
     * @param $path
     * @param $id
     * @param $slug
     *
     * @return mixed
     */
    public static function replaceSlugInPath($path, $id, $slug): string|array|null
    {
        return preg_replace('#(.+/)?.*(-'.$id.'/)#', '$1'.$slug.'$2', (string) $path);
    }

    /**
     * Create a snapshot of a given page.
     */
    public function makePageSnapshot(PageInterface $page)
    {

        $pageSnapshotClass = $this->pageSnapshotManager->getClassName();

        /** @var \Networking\InitCmsBundle\Model\PageSnapshotInterface $pageSnapshot */
        $pageSnapshot = new $pageSnapshotClass($page);


        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function (object $object, string $format, array $context): int {
                return $object->getId();
            },
            AbstractNormalizer::CALLBACKS => [
                'translations' => function ( $innerObject, object $outerObject, string $attributeName, string $format = null, array $context = []): array {
                    return $outerObject instanceof PageInterface ? $outerObject->convertTranslationsToIntegerArray():[] ;
                },
                'originals' => function ( $innerObject, object $outerObject, string $attributeName, string $format = null, array $context = []): array {
                    return $outerObject instanceof PageInterface ? $outerObject->convertOriginalsToIntegerArray():[];
                },
                'parent' => function ( $innerObject, object $outerObject, string $attributeName, string $format = null, array $context = []): ?int {
                    return $outerObject instanceof PageInterface ? $outerObject->convertParentToInteger():null;
                },
                'alias' => function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []): ?int {
                    return $outerObject instanceof PageInterface ? $outerObject->convertAliasToInteger():null;
                },
                'parents' => function ( $innerObject, object $outerObject, string $attributeName, string $format = null, array $context = []): array {
                    return $outerObject instanceof PageInterface ? $outerObject->convertParentsToArray():[];
                },
                'children' => function ( $innerObject, object $outerObject, string $attributeName, string $format = null, array $context = []): array {
                    return $outerObject instanceof PageInterface ? $outerObject->convertChildrenToIntegerArray():[];
                },
                'original' => function ( $innerObject, object $outerObject, string $attributeName, string $format = null, array $context = []): ?int {
                    return $outerObject instanceof PageInterface ? $outerObject->getId():null;
                },
            ],
            AbstractNormalizer::IGNORED_ATTRIBUTES => [
                'allChildren',
                'allTranslations',
                'snapshots',
                'snapshot',
                'route',
                'routes',
                'oldTitle',
                'tags',
                'contentRoute',
                'menuItem',
                'oringal',
                'directTranslation',
                '__initializer__',
                '__cloner__',
                '__isInitialized__'
            ],
            AbstractNormalizer::REQUIRE_ALL_PROPERTIES => false,
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => false,
        ];

        $data = $this->serializer->serialize($page, 'json', $defaultContext);


        $pageSnapshot->setVersionedData($data)
            ->setPage($page);

        if ($oldPageSnapshot = $page->getSnapshot()) {
            $snapshotContentRoute = $oldPageSnapshot->getContentRoute();
        } else {
            $contentRouteClass = $this->contentRouteManager->getClassName();
            /** @var \Networking\InitCmsBundle\Model\ContentRouteInterface $snapshotContentRoute */
            $snapshotContentRoute = new $contentRouteClass();
        }

        $pageSnapshot->setContentRoute($snapshotContentRoute);
        $pageSnapshot->setPath(self::getPageRoutePath($page->getPath()));

        $om = $this->registry->getManagerForClass($pageSnapshotClass);

        $om->persist($pageSnapshot);
        $om->flush();

        $snapshotContentRoute->setPath(self::getPageRoutePath($page->getPath()));
        $snapshotContentRoute->setObjectId($pageSnapshot->getId());


        if ($oldPageSnapshot && ($oldPageSnapshot->getPath() != self::getPageRoutePath($page->getPath()))) {
            $this->pageCache->clean();
        }

        $om = $this->registry->getManagerForClass($snapshotContentRoute::class);
        $om->persist($snapshotContentRoute);
        $om->flush();
    }

    /**
     * Unserialize the PageSnapshot data into a page object.
     *
     * @param bool                  $unserializeTranslations
     * @return array|\JMS\Serializer\scalar|object
     */
    public function unserializePageSnapshotData(PageSnapshotInterface $pageSnapshot, $unserializeTranslations = true)
    {

        if(str_contains($pageSnapshot->getResourceName(), 'Proxies\__CG__\\')){
            $pageSnapshot->setResourceName(str_replace('Proxies\__CG__\\', '', $pageSnapshot->getResourceName()));
        }

        $pageManager = $this->pageManager;
        $context = [
            AbstractNormalizer::CALLBACKS => [
                'translations' => function ( $innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) use($pageManager): array {
                    $translations = [];
                    foreach ($innerObject as $key => $page){
                            $translations[$key] = $pageManager->find(
                                $page->getId()
                            );
                        $page->setTranslations($translations);
                    }
                    return  $translations ;
                },
                'original' => function ( $innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) use($pageManager): ?PageInterface {
                    return $innerObject ? $pageManager->find(
                        $innerObject
                    ):null;
                },
                'alias' => function ( $innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) use($pageManager): ?PageInterface {
                    return $innerObject ? $pageManager->find(
                        $innerObject
                    ):null;
                },

            ],
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
        ];
        return $this->serializer->deserialize($pageSnapshot->getVersionedData(), $pageSnapshot->getResourceName(), 'json', $context);

        $context = new PageSnapshotDeserializationContext();
        $context->setDeserializeTranslations($unserializeTranslations);

        return $this->serializer->deserialize($pageSnapshot->getVersionedData(), $pageSnapshot->getResourceName(), 'json', $context);
    }

    /**
     * create a copy of a given page object in a given locale.
     *
     * @param $locale
     * @return PageInterface
     */
    public function makeTranslationCopy(PageInterface $page, $locale)
    {
        $pageClass = $this->pageManager->getClassName();
        /** @var PageInterface $pageCopy */
        $pageCopy = new $pageClass();

        $pageCopy->setPageName($page->getPageName());
        $pageCopy->setMetaTitle($page->getMetaTitle());
        $pageCopy->setUrl($page->getUrl());
        $pageCopy->setMetaKeyword($page->getMetaKeyword());
        $pageCopy->setMetaDescription($page->getMetaDescription());
        $pageCopy->setActiveFrom($page->getActiveFrom());
        $pageCopy->setIsHome($page->getIsHome());
        $pageCopy->setLocale($locale);
        $pageCopy->setTemplateName($page->getTemplateName());
        $pageCopy->getOriginals()->add($page);

        $layoutBlocks = $page->getLayoutBlock();
	    $om = $this->registry->getManager();

        foreach ($layoutBlocks as $layoutBlock) {

            $newLayoutBlock = clone $layoutBlock;
            $newLayoutBlock->setPage($pageCopy);
            $om->persist($newLayoutBlock);
        }

	    $om->persist($pageCopy);

        $om->flush();

        return $pageCopy;
    }

    /**
     * create a copy of a given page object.
     *
     *
     * @return PageInterface
     */
    public function makePageCopy(PageInterface $page)
    {
        $pageClass = $this->pageManager->getClassName();
        /** @var PageInterface $pageCopy */
        $pageCopy = new $pageClass();

        $now = new \DateTime();

        $postfix = sprintf(' copy %s', $now->format('d.m.Y H:i:s'));

        $pageCopy->setPageName($page->getPageName().$postfix);
        $pageCopy->setMetaTitle($page->getMetaTitle());
        $pageCopy->setUrl($page->getUrl().$postfix);
        $pageCopy->setMetaKeyword($page->getMetaKeyword());
        $pageCopy->setMetaDescription($page->getMetaDescription());
        $pageCopy->setActiveFrom($page->getActiveFrom());
        $pageCopy->setIsHome(false);
        $pageCopy->setTemplateName($page->getTemplateName());
        $pageCopy->setLocale($page->getLocale());

        $layoutBlocks = $page->getLayoutBlock();

        $om = $this->registry->getManager();

        foreach ($layoutBlocks as $layoutBlock) {

            /** @var $newLayoutBlock \Networking\InitCmsBundle\Model\LayoutBlockInterface */
            $newLayoutBlock = clone $layoutBlock;


            $content = $om->getRepository($newLayoutBlock::class)->find(
                $newLayoutBlock->getObjectId()
            );
            $newContent = clone $content;

            $om->persist($newContent);
            $om->flush();

            $newLayoutBlock->setObjectId($newContent->getId());
            $newLayoutBlock->setPage($pageCopy);

            $om->persist($newLayoutBlock);
        }

        $om->persist($pageCopy);

        $om->flush();

        return $pageCopy;
    }

    /**
     * Fills the request object with the content route parameters if found.
     *
     *
     * @return Request
     */
    public function matchContentRouteRequest(Request $request)
    {
        $requestParams = $this->router->matchRequest($request);

        if (is_array($requestParams) && !empty($requestParams)) {
            $request->attributes->add($requestParams);

            unset($requestParams['_route']);
            unset($requestParams['_controller']);
            $request->attributes->set('_route_params', $requestParams);

            $configuration = $request->attributes->get('_template');
            $request->attributes->set('_template', $configuration->template);
            $request->attributes->set('_template_vars', $configuration->vars);
            $request->attributes->set('_template_streamable', $configuration->stream);
        }

        return $request;
    }

    /**
     * Returns if a page is active or inactive based on json string from page snapshot.
     *
     * @param $jsonString
     *
     * @return bool
     */
    public function jsonPageIsActive($jsonString)
    {
        $page = json_decode((string) $jsonString, true, 512, JSON_THROW_ON_ERROR);

        $now = new \DateTime();

        $activeStart = array_key_exists('active_from', $page) && $page['active_from'] ? new \DateTime($page['active_from']) : new \DateTime();
        $activeEnd = array_key_exists('active_to', $page) && $page['active_to'] ? new \DateTime($page['active_to']) : new \DateTime();

        if ($now->getTimestamp() >= $activeStart->getTimestamp() &&
            $now->getTimestamp() <= $activeEnd->getTimestamp()
        ) {
            return $page['status'] == PageInterface::STATUS_PUBLISHED;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isAllowLocaleCookie()
    {
        return $this->allowLocaleCookie;
    }

    /**
     * @return PageHelper
     */
    public function setAllowLocaleCookie(bool $allowLocaleCookie)
    {
        $this->allowLocaleCookie = $allowLocaleCookie;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSingleLanguage()
    {
        return $this->singleLanguage;
    }

    /**
     * @return PageHelper
     */
    public function setSingleLanguage(bool $singleLanguage)
    {
        $this->singleLanguage = $singleLanguage;

        return $this;
    }

    /**
     * @param $locale
     * @return Cookie[]
     */
    public function setLocaleCookies($locale){

        $params = session_get_cookie_params();
        $samesite = array_key_exists('samesite', $params)?$params['samesite']:Cookie::SAMESITE_LAX;
        $secure = $samesite ==='none'?true:$params['secure'];

        $cookies = [];
        $expires = 0;
        if($params['lifetime']){
            $expires = time()+$params['lifetime'];
        }

        $cookies[] = Cookie::create(
            '_locale',
            $locale,
            $expires,
            $params['path'],
            $params['domain'],
            $secure,
            $params['httponly'],
            false,
            $samesite
        );

        //fallback if browser does not support samesite=none
        if($samesite === 'none'){
            $cookies[] = Cookie::create(
                '_locale_legacy',
                $locale,
                $expires,
                $params['path'],
                $params['domain'],
                $secure,
                $params['httponly'],
                false
            );


        }

        return $cookies;
    }

    public function getSerializer()
    {
        return $this->serializer;
    }
}
