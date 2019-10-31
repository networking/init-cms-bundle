<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\tests\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Networking\InitCmsBundle\Entity\ContentRouteManager;
use Networking\InitCmsBundle\Entity\PageManager;
use Networking\InitCmsBundle\Entity\PageSnapshot;
use Networking\InitCmsBundle\Entity\PageSnapshotManager;
use Networking\InitCmsBundle\Helper\LanguageSwitcherHelper;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Lib\PhpCache;
use PHPUnit\Framework\TestCase;
use Networking\InitCmsBundle\Controller\FrontendPageController;
use Networking\InitCmsBundle\Model\Page;
use Networking\InitCmsBundle\Model\PageInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Twig\Environment;

/** @author sonja brodersen s.brodersen@networking.ch */
class FrontendPageControllerTest extends TestCase
{
    public function testIndexActionWithAccessDeniedException()
    {

        // because no user is authenticated: a AuthenticationCredentialsNotFoundException
        $this->expectException(
            'Symfony\Component\Security\Core\Exception\AccessDeniedException'
        );

        //Mocks
        $mockPage = $this->getMockBuilder('\Networking\InitCmsBundle\Model\Page')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPage->expects($this->once())
            ->method('getVisibility')
            ->will($this->returnValue(PageInterface::VISIBILITY_PROTECTED));

        $mockRequest = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $mockRequest->expects($this->at(0))
            ->method('get')
            ->with('_content')
            ->will($this->returnValue($mockPage));

        $mockRequest->expects($this->at(1))
            ->method('get')
            ->with('_template');

        $mockRequest->expects($this->at(2))
            ->method('get')
            ->with('_content')
            ->will($this->returnValue($mockPage));

        $mockTokenStorage = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage')
            ->disableOriginalConstructor()
            ->getMock();

        $mockAccessDecisionManager = $this->getMockBuilder('Symfony\Component\Security\Core\Authorization\AccessDecisionManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mockAuthorisationChecker = $this->getMockBuilder('Symfony\Component\Security\Core\Authorization\AuthorizationChecker')
            ->disableOriginalConstructor()
            ->getMock();

        $property = new \ReflectionProperty('Symfony\Component\Security\Core\Authorization\AuthorizationChecker', 'tokenStorage');
        $property->setAccessible(true);
        $property->setValue($mockAuthorisationChecker, $mockTokenStorage);

        $property = new \ReflectionProperty('Symfony\Component\Security\Core\Authorization\AuthorizationChecker', 'accessDecisionManager');
        $property->setAccessible(true);
        $property->setValue($mockAuthorisationChecker, $mockAccessDecisionManager);



        $mockContainer = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')
            ->disableOriginalConstructor()
            ->getMock();

        //cache class
        $mockCacheClass = $this->getMockBuilder(PhpCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPool = $this->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPageManager= $this->getMockBuilder(PageManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockLanguageSwitcher= $this->getMockBuilder(LanguageSwitcherHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPageHelper = $this->getMockBuilder(PageHelper::class)
            ->disableOriginalConstructor()
            ->getMock();



        $mockTokenStorage->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue(new AnonymousToken('anon', 'anon')));

        $mockAccessDecisionManager->expects($this->any())
            ->method('decide')
            ->willReturn(false);

        $mockAuthorisationChecker->expects($this->any())
            ->method('isGranted')
            ->with('ROLE_USER')
            ->will($this->returnValue(false));

        $mockContainer->expects($this->any(0))
            ->method('has')
            ->with('security.token_storage')
            ->will($this->returnValue(true));

        $mockContainer->expects($this->at(1))
            ->method('get')
            ->with('security.token_storage')
            ->will($this->returnValue($mockTokenStorage));


        // Controller
        $controller = new FrontendPageController(
            $mockCacheClass,
            $mockTokenStorage,
            $mockAuthorisationChecker,
            $mockPool,
            $mockLanguageSwitcher,
            $mockPageManager,
            $mockPageHelper
        );
        $controller->setContainer($mockContainer);
        $this->assertInstanceOf(
            'Networking\InitCmsBundle\Controller\FrontendPageController',
            $controller,
            'Controller ist a DefaultController'
        );

        $controller->indexAction($mockRequest);
    }

    public function testIndexActionWithNotFoundHttpException()
    {
        $this->expectException('\Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        //Mocks
        // page
        $mockPage = $this->getMockBuilder('\Networking\InitCmsBundle\Model\Page')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPage->expects($this->once())
            ->method('getVisibility')
            ->will($this->returnValue(Page::VISIBILITY_PUBLIC));
        $mockPage->expects($this->exactly(2))
            ->method('getStatus')
            ->will($this->returnValue(Page::STATUS_DRAFT));

        // request
        $mockRequest = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $mockRequest->expects($this->at(0))
            ->method('get')
            ->with('_content')
            ->will($this->returnValue($mockPage));

        $mockRequest->expects($this->at(1))
            ->method('get')
            ->with('_template');

        $mockRequest->expects($this->at(2))
            ->method('get')
            ->with('_content')
            ->will($this->returnValue($mockPage));
        //security context
        $mockTokenStorage = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage')
            ->disableOriginalConstructor()
            ->getMock();

        $mockAuthorisationChecker = $this->getMockBuilder('Symfony\Component\Security\Core\Authorization\AuthorizationChecker')
            ->disableOriginalConstructor()
            ->getMock();
        $property = new \ReflectionProperty('Symfony\Component\Security\Core\Authorization\AuthorizationChecker', 'tokenStorage');
        $property->setAccessible(true);
        $property->setValue($mockAuthorisationChecker, $mockTokenStorage);

        $mockTokenStorage->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue(null));

        $mockAuthorisationChecker->expects($this->any())
            ->method('isGranted')
            ->with('ROLE_SONATA_ADMIN')
            ->will($this->returnValue(false));
        //Container
        $mockContainer = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')
            ->disableOriginalConstructor()
            ->getMock();
        //cache class
        $mockCacheClass = $this->getMockBuilder(PhpCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPool = $this->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPageManager= $this->getMockBuilder(PageManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockLanguageSwitcher= $this->getMockBuilder(LanguageSwitcherHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPageHelper = $this->getMockBuilder(PageHelper::class)
            ->disableOriginalConstructor()
            ->getMock();


        $mockContainer->expects($this->at(0))
            ->method('has')
            ->with('security.token_storage')
            ->will($this->returnValue(true));

        $mockContainer->expects($this->at(1))
            ->method('get')
            ->with('security.token_storage')
            ->will($this->returnValue($mockTokenStorage));

        // Controller
        $controller = new FrontendPageController(
            $mockCacheClass,
            $mockTokenStorage,
            $mockAuthorisationChecker,
            $mockPool,
            $mockLanguageSwitcher,
            $mockPageManager,
            $mockPageHelper
        );
        $controller->setContainer($mockContainer);
        $this->assertInstanceOf(
            'Networking\InitCmsBundle\Controller\FrontendPageController',
            $controller,
            'Controller ist a DefaultController'
        );

        $controller->indexAction($mockRequest);
    }

    public function testLiveAction()
    {

        // MOCKS
        $mockPage = $this->createMock('Networking\InitCmsBundle\Model\Page');
        $mockPage->expects($this->once())
            ->method('getVisibility')
            ->will($this->returnValue(PageInterface::VISIBILITY_PUBLIC));
        $mockPage->expects($this->once())
            ->method('isActive')
            ->will($this->returnValue(true));

        $mockSnapshot = $this->getMockBuilder(PageSnapshot::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPageHelper = $this->getMockBuilder(PageHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPageHelper->expects($this->once())
            ->method('unserializePageSnapshotData')
            ->will($this->returnValue($mockPage));

        $mockPageHelper->expects($this->once())
            ->method('isAllowLocaleCookie')
            ->will($this->returnValue(true));

        $mockPageHelper->expects($this->once())
            ->method('isSingleLanguage')
            ->will($this->returnValue(false));

        //cache class
        $mockCacheClass = $this->getMockBuilder(PhpCache::class)
            ->disableOriginalConstructor()
            ->getMock();


        $mockPool = $this->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPageManager= $this->getMockBuilder(PageManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockLanguageSwitcher= $this->getMockBuilder(LanguageSwitcherHelper::class)
            ->disableOriginalConstructor()
            ->getMock();


        //security context
        $mockAuthorisationChecker = $this->getMockBuilder(AuthorizationChecker::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockAuthorisationChecker->expects($this->any())
            ->method('isGranted')
            ->with('ROLE_USER')
            ->will($this->returnValue(true));

        $mockTokenStorage = $this->getMockBuilder(TokenStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        //templating
        $mockTemplating = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockResponse = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Response')
            ->getMock();

        $mockTemplating->expects($this->once())
            ->method('render')
            ->will($this->returnValue($mockResponse));

        //request
        $mockRequest = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $mockRequest->expects($this->at(0))
            ->method('get')
            ->with('_content')
            ->will($this->returnValue($mockSnapshot));

        $mockRequest->expects($this->at(1))
            ->method('get')
            ->with('_template')
            ->will($this->returnValue('@DemoInitCms/Default/one_column.html.twig'));

        $mockRequest->expects($this->at(2))
            ->method('get')
            ->with('_content')
            ->will($this->returnValue($mockSnapshot));

        //Container
        $mockContainer = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')
            ->disableOriginalConstructor()
            ->getMock();


        $mockContainer->expects($this->at(0))
            ->method('has')
            ->with('security.token_storage')
            ->will($this->returnValue(true));

        $mockContainer->expects($this->at(1))
            ->method('get')
            ->with('security.token_storage')
            ->will($this->returnValue($mockTokenStorage));


        $mockContainer->expects($this->at(2))
            ->method('has')
            ->with('templating')
            ->will($this->returnValue(false));

        $mockContainer->expects($this->at(3))
            ->method('has')
            ->with('twig')
            ->will($this->returnValue(true));

        $mockContainer->expects($this->at(4))
            ->method('get')
            ->with('twig')
            ->will($this->returnValue($mockTemplating));



        // controller
        $controller = new FrontendPageController(
            $mockCacheClass,
            $mockTokenStorage,
            $mockAuthorisationChecker,
            $mockPool,
            $mockLanguageSwitcher,
            $mockPageManager,
            $mockPageHelper
        );
        $controller->setContainer($mockContainer);
        $response = $controller->indexAction($mockRequest);

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response, 'response object returned');
    }

    public function testRequest()
    {
        $request = new Request();
        $request->setLocale('de_CH');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Request', $request, 'request class');
        $this->assertEquals('de_CH', $request->getLocale(), 'request locale');
    }

    /**
     * home.
     */
    public function testHomeAction()
    {
        $mockPage = $this->createMock('\Networking\InitCmsBundle\Model\Page');
        $mockSnapshot = $this->createMock(
            '\Networking\InitCmsBundle\Model\PageSnapshot',
            [$mockPage]
        );

        $mockSnapshot->expects($this->once())
            ->method('getVersionedData')
            ->will($this->returnValue(json_encode($mockPage)));

        $mockSnapshot->expects($this->once())
            ->method('getResourceName')
            ->will($this->returnValue(Page::class));

        $mockPage->expects($this->once())
            ->method('getVisibility')
            ->will($this->returnValue(PageInterface::VISIBILITY_PUBLIC));

        $mockPage->expects($this->once())
            ->method('isActive')
            ->will($this->returnValue(true));

        $mockSerializer = $this->getMockBuilder('\JMS\Serializer\SerializerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSerializer->expects($this->once())
            ->method('deserialize')
            ->willReturn($mockPage);

        //security context
        $mockAuthorisationChecker = $this->getMockBuilder(AuthorizationChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAuthorisationChecker->expects($this->any())
            ->method('isGranted')
            ->with('ROLE_USER')
            ->will($this->returnValue(true));
        $mockTokenStorage = $this->getMockBuilder(TokenStorage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockRegistry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockPageManager = $this->getMockBuilder(PageManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockPageSnapshotManager = $this->getMockBuilder(PageSnapshotManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockContentRouteManager = $this->getMockBuilder(ContentRouteManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockPool = $this->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->getMock();
        

        $mockLanguageSwitcher= $this->getMockBuilder(LanguageSwitcherHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $_SERVER = [
            'PATH_INFO' => '/',
            'SCRIPT_NAME' => 'app.php',
        ];
        $templateParams = [
            'template' => '@DemoInitCms/Default/one_column.html.twig',
            'vars' => [],
            'isStreamable' => false,
        ];
        $template = new Template($templateParams);
        $requestParams = [
            '_content' => $mockSnapshot,
            '_template' => $template,
        ];
        //templating
        $mockTwig = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockResponse = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Response')
            ->getMock();

        $mockTwig->expects($this->once())
            ->method('render')
            ->will($this->returnValue($mockResponse));
        //request
        $request = new Request([], [], [], [], [], $_SERVER);
        $request->attributes->set('_route', 'networking_init_cms_default');
        $request->attributes->set('_locale', 'en');

        //dynamic router
        $mockDynamicRouter = $this->getMockBuilder('\Symfony\Cmf\Component\Routing\DynamicRouter')
            ->disableOriginalConstructor()
            ->getMock();
        $mockDynamicRouter->expects($this->once())
            ->method('matchRequest')
            ->with($request)
            ->willReturn($requestParams);

        //container
        $mockContainer = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')
            ->disableOriginalConstructor()
            ->getMock();

        //cache class
        $mockCacheClass = $this->getMockBuilder(PhpCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pageHelper = new \Networking\InitCmsBundle\Helper\PageHelper(
            $mockSerializer, $mockRegistry,  $mockPageManager, $mockPageSnapshotManager, $mockContentRouteManager,
            $mockDynamicRouter, $mockCacheClass, true, false
        );



        $mockContainer->expects($this->at(0))
            ->method('has')
            ->with('security.token_storage')
            ->will($this->returnValue(true));

        $mockContainer->expects($this->at(1))
            ->method('get')
            ->with('security.token_storage')
            ->will($this->returnValue($mockTokenStorage));


        $mockContainer->expects($this->at(2))
            ->method('has')
            ->with('templating')
            ->will($this->returnValue(false));

        $mockContainer->expects($this->at(3))
            ->method('has')
            ->with('twig')
            ->will($this->returnValue(true));

        $mockContainer->expects($this->at(4))
            ->method('get')
            ->with('twig')
            ->will($this->returnValue($mockTwig));


        $requestAfter = clone $request;

        $requestAfter->attributes->add($requestParams);
        unset($requestParams['_route']);
        unset($requestParams['_controller']);
        $requestAfter->attributes->set('_route_params', $requestParams);

        $configuration = $requestAfter->attributes->get('_template');
        $requestAfter->attributes->set('_template', $configuration->getTemplate());
        $requestAfter->attributes->set('_template_vars', $configuration->getVars());
        $requestAfter->attributes->set('_template_streamable', $configuration->isStreamable());

        // controller
        $controller = new FrontendPageController(
            $mockCacheClass,
            $mockTokenStorage,
            $mockAuthorisationChecker,
            $mockPool,
            $mockLanguageSwitcher,
            $mockPageManager,
            $pageHelper
        );
        $controller->setContainer($mockContainer);
        $response = $controller->homeAction($request);

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response, 'response object returned');
    }

    public function testChangeAdminLanguageAction()
    {
        // session
        $session = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $session->expects($this->once())
            ->method('set')
            ->with(
                $this->equalTo('admin/_locale'),
                $this->equalTo('xy')
            );
        // request
        $request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($session));
        // request headers
        $headers = $this->createMock('\Symfony\Component\HttpFoundation\HeaderBag');
        $headers->expects($this->once())
            ->method('get')
            ->will($this->returnValue('/test/'))
            ->with($this->equalTo('referer'));
        $request->headers = $headers;
        //cache class
        $mockCacheClass = $this->getMockBuilder(PhpCache::class)
            ->disableOriginalConstructor()
            ->getMock();
        //security context
        $mockAuthorisationChecker = $this->getMockBuilder(AuthorizationChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockTokenStorage = $this->getMockBuilder(TokenStorage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockPageManager = $this->getMockBuilder(PageManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPool = $this->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPageHelper = $this->getMockBuilder(PageHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockLanguageSwitcher= $this->getMockBuilder(LanguageSwitcherHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        // controller test
        // controller
        $controller = new FrontendPageController(
            $mockCacheClass,
            $mockTokenStorage,
            $mockAuthorisationChecker,
            $mockPool,
            $mockLanguageSwitcher,
            $mockPageManager,
            $mockPageHelper
        );
        $response = $controller->changeAdminLanguageAction($request, 'xy');
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response, 'Redirect returned');
    }
}
