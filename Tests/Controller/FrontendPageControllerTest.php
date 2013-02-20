<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Tests\Controller;

use \Networking\InitCmsBundle\Controller\FrontendPageController,
    \Networking\InitCmsBundle\Helper\LanguageSwitcherHelper,
    \Symfony\Component\HttpFoundation\Request,
    \Symfony\Component\HttpFoundation\Response,
    \Networking\InitCmsBundle\Entity\Page,
    \Symfony\Component\Security\Core\Exception\AccessDeniedException,
    \Symfony\Component\HttpKernel\Exception\NotFoundHttpException,
    \Symfony\Component\Security\Core\SecurityContext,
Symfony\Bundle\FrameworkBundle\Routing\Router;

/** @author sonja brodersen s.brodersen@networking.ch*/

class FrontendPageControllerTest extends \PHPUnit_Framework_TestCase
{
	/**
     *
	 */
	public function testIndexActionWithAccessDeniedException()
	{
        $this->setExpectedException('\Symfony\Component\Security\Core\Exception\AccessDeniedException');
        // because no user is authenticated: a AuthenticationCredentialsNotFoundException
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException');

        //Mocks
        $mockPage  = $this->getMockBuilder('\Networking\InitCmsBundle\Entity\Page')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPage->expects($this->once())
            ->method('getVisibility')
            ->will($this->returnValue(Page::VISIBILITY_PROTECTED));

        $mockRequest = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
				->disableOriginalConstructor()
				->getMock();
        $mockRequest->expects($this->once())
            ->method('get')
            ->with('_content')
            ->will($this->returnValue($mockPage));

        $mockSecurityContext = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSecurityContext->expects($this->any())
            ->method('isGranted')
            ->with('ROLE_USER')
            ->will($this->returnValue(false));

        $mockContainer = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')
            ->disableOriginalConstructor()
            ->getMock();
        $mockContainer->expects($this->any())
            ->method('get')
            ->with('security.context')
            ->will($this->returnValue($mockSecurityContext));

        // Controller
		$controller = new FrontendPageController();
        $controller->setContainer($mockContainer);
        $this->assertInstanceOf('Networking\InitCmsBundle\Controller\FrontendPageController', $controller, 'Controller ist a DefaultController');

        $controller->indexAction($mockRequest);
	}

    /**
     */
    public function testIndexActionWithNotFoundHttpException()
	{
        $this->setExpectedException('\Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        // because no user is authenticated: a AuthenticationCredentialsNotFoundException
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException');

        //Mocks
        // page
        $mockPage  = $this->getMockBuilder('\Networking\InitCmsBundle\Entity\Page')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPage->expects($this->once())
            ->method('getVisibility')
            ->will($this->returnValue(Page::VISIBILITY_PUBLIC));
        $mockPage->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue(Page::STATUS_DRAFT));

        // request
        $mockRequest = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
				->disableOriginalConstructor()
				->getMock();
        $mockRequest->expects($this->once())
            ->method('get')
            ->with('_content')
            ->will($this->returnValue($mockPage));
        //security context
        $mockSecurityContext = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSecurityContext->expects($this->any())
            ->method('isGranted')
            ->with('ROLE_SONATA_ADMIN')
            ->will($this->returnValue(false));
        //Container
        $mockContainer = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')
            ->disableOriginalConstructor()
            ->getMock();
        $mockContainer->expects($this->any())
            ->method('get')
            ->with('security.context')
            ->will($this->returnValue($mockSecurityContext));

        // Controller
		$controller = new FrontendPageController();
        $controller->setContainer($mockContainer);
        $this->assertInstanceOf('Networking\InitCmsBundle\Controller\FrontendPageController', $controller, 'Controller ist a DefaultController');

        $controller->indexAction($mockRequest);
	}


    public function testLiveAction()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException');

        // MOCKS
        $mockPage = $this->getMock('Networking\InitCmsBundle\Entity\Page');
        $mockPage->expects($this->once())
            ->method('getVisibility')
            ->will($this->returnValue(Page::VISIBILITY_PUBLIC));
        $mockSnapshot = $this->getMockBuilder('Networking\InitCmsBundle\Entity\PageSnapshot')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSnapshot->expects($this->once())
            ->method('getVersionedData')
            ->will($this->returnValue($mockPage));

        $mockSerializer = $this->getMockBuilder('\JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSerializer->expects($this->once())
            ->method('deserialize')
            ->will($this->returnValue($mockPage));

        //security context
        $mockSecurityContext = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSecurityContext->expects($this->any())
            ->method('isGranted')
            ->with('ROLE_USER')
            ->will($this->returnValue(true));
        //request
        $mockRequest = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
				->disableOriginalConstructor()
				->getMock();
        $mockRequest->expects($this->once())
            ->method('get')
            ->with('_content')
            ->will($this->returnValue($mockSnapshot));
        //Container
        $mockContainer = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')
            ->disableOriginalConstructor()
            ->getMock();
        $mockContainer->expects($this->at(0))
            ->method('get')
            ->with('serializer')
            ->will($this->returnValue($mockSerializer));
        $mockContainer->expects($this->at(1))
            ->method('get')
            ->with('security.context')
            ->will($this->returnValue($mockSecurityContext));

        // controller
        $controller = new FrontendPageController();
        $controller->setContainer($mockContainer);
        $response = $controller->liveAction($mockRequest);
        $this->assertEqual(array('page'=>$mockPage), $response);

    }

	/**
	 *
	 */
	public function testRequest()
	{
		$request = new Request();
		$request->setLocale('de_CH');
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Request', $request, 'request class');
		$this->assertEquals('de_CH', $request->getLocale(), 'request locale');
	}

	/**
	 * home
  	 */
	public function testHomeAction()
	{
		$page = new \Networking\InitCmsBundle\Entity\Page;
        // repo
		$mockRepo = $this->getMock('\Doctrine\ORM\Repository', array('findOneBy'));

        //Doctrine
		$mockDoctrine = $this->getMock('\Doctrine\ORM\Repository', array('getRepository'));
		$mockDoctrine->expects($this->once())
				->method('getRepository')
				->with($this->equalTo('NetworkingInitCmsBundle:Page'))
				->will($this->returnValue($mockRepo));
		$mockRepo->expects($this->once())
				->method('findOneBy')
				->with($this->equalTo(array('isHome' => true, 'locale' =>'en')))
				->will($this->returnValue($page));

        //request
        $mockRequest = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
				->disableOriginalConstructor()
				->getMock();
        $mockRequest->expects($this->once())
            ->method('getLocale')
            ->will($this->returnValue('en'));

        //container
		$mockContainer = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')
            ->disableOriginalConstructor()
            ->getMock();
        $mockContainer->expects($this->once())
            ->method('has')
            ->with('doctrine')
            ->will($this->returnValue(true));
        $mockContainer->expects($this->at(1))
            ->method('get')
            ->with('doctrine')
            ->will($this->returnValue($mockDoctrine));
        $mockContainer->expects($this->at(2))
            ->method('get')
            ->with('request')
            ->will($this->returnValue($mockRequest));

        $controller = new FrontendPageController();
        $controller->setContainer($mockContainer);
        $response = $controller->homeAction();
		$this->assertInternalType('array', $response, 'response is an array');
		$this->assertEquals(array('page'=>$page), $response, 'response has got a page');
	}


	public function testChangeAdminLanguageAction()
	{
		// session
		$session = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Session')
				->disableOriginalConstructor()
				->getMock();
		$session->expects($this->once())
				->method('set')
				->with($this->equalTo('admin/_locale'),
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
		$headers = $this->getMock('\Symfony\Component\HttpFoundation\HeaderBag');
		$headers->expects($this->once())
			->method('get')
			->will($this->returnValue('/test/'))
			->with($this->equalTo('referer'));
		$request->headers = $headers;
		// controller test
		$controller = new FrontendPageController();
		$response = $controller->changeAdminLanguageAction($request, 'xy');
		$this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
	}

//	public function testChangeLanguageActionWithCurrentLocal()
//    {
//        $mockHeaders = $this->getMock('\Symfony\Component\HttpFoundation\HeaderBag');
//		$mockHeaders->expects($this->once())
//			->method('get')
//            ->with($this->equalTo('referer'))
//            ->will($this->returnValue('/the same/'));
//
//
//        $mockSession = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Session')
//				->disableOriginalConstructor()
//				->getMock();
//        $mockSession->expects($this->once())
//            ->method('set')
//            ->with('_locale', 'foo');
//
//
//		// request
//		$mockRequest = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
//				->disableOriginalConstructor()
//				->getMock();
//        $mockRequest->expects($this->once())
//            ->method('getSession')
//            ->will($this->returnValue($mockSession));
//        $mockRequest->headers = $mockHeaders;
//
//        $languageSwitcherHelper = new LanguageSwitcherHelper();
//
//        $mockRouter = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\Router')
//            ->disableOriginalConstructor()
//            ->getMock();
//
//        $mockRouter->expects($this->once())
//            ->method('generate')
//            ->will($this->returnValue('/some_url/'));
//
//        //container
//		$mockContainer = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')
//            ->disableOriginalConstructor()
//            ->getMock();
//        $mockContainer->expects($this->at(0))
//            ->method('get')
//            ->with('networking_init_cms.page.helper.language_switcher')
//            ->will($this->returnValue($languageSwitcherHelper));
//        $mockContainer->expects($this->at(1))
//            ->method('get')
//            ->with('router')
//            ->will($this->returnValue($mockRouter));
//
//
//
//		$controller = new FrontendPageController();
//        $controller->setContainer($mockContainer);
//
//		$response = $controller->changeLanguageAction($mockRequest, 'foo');
//        $this->assertEquals('/the same/', $response->getTargetUrl());
//	}



//
//    public function testChangeLanguageAction()
//    {
//        $this->markTestIncomplete('change this test');
//        $mockSession = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Session')
//				->disableOriginalConstructor()
//				->getMock();
//        $mockSession->expects($this->once())
//            ->method('set')
//            ->with('_locale', 'foo');
//
//        $mockLangSwitcherHelper = $this->getMock('\Networking\InitCmsBundle\Helper\LanguageSwitcherHelper');
//        $mockLangSwitcherHelper->expects($this->once())
//            ->method('getPathInfo')
//            ->will($this->returnValue('/old url/'));
//        $mockLangSwitcherHelper->expects($this->once())
//            ->method('getTranslationRoute')
//            ->will($this->returnValue('/new url/'));
//        $mockRouter = $this->getMockBuilder('Symfony\Cmf\Bundle\RoutingExtraBundle\Routing\DynamicRouter')
//            ->disableOriginalConstructor()
//            ->getMock();
//        $mockRouter->expects($this->once())
//            ->method('generate')
//            ->will($this->returnValue('/Router NEW URL/'));
//
//        $mockContainer = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')
//            ->disableOriginalConstructor()
//            ->getMock();
//        $mockContainer->expects($this->at(0))
//            ->method('get')
//            ->with('networking_init_cms.page.helper.language_switcher')
//            ->will($this->returnValue($mockLangSwitcherHelper));
//        $mockContainer->expects($this->at(1))
//            ->method('get')
//            ->with('router')
//            ->will($this->returnValue($mockRouter));
//        $mockHeaders = $this->getMock('\Symfony\Component\HttpFoundation\HeaderBag');
//		$mockHeaders->expects($this->any())
//			->method('get')
//			->will($this->returnValue('/the new/'))
//			->with($this->equalTo('referer'));
//
//		// request
//		$mockRequest = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
//				->disableOriginalConstructor()
//				->getMock();
//        $mockRequest->expects($this->once())
//            ->method('getLocale')
//            ->will($this->returnValue('bar'));
//        $mockRequest->expects($this->once())
//            ->method('getSession')
//            ->will($this->returnValue($mockSession));
//        $mockRequest->headers = $mockHeaders;
//		$controller = new FrontendPageController();
//        $controller->setContainer($mockContainer);
//		$response = $controller->changeLanguageAction($mockRequest, 'foo');
//        $this->assertEquals('/Router NEW URL/', $response->getTargetUrl());
//	}

}

