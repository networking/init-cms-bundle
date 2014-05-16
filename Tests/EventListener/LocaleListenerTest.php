<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Tests\EventListener;

use Networking\InitCmsBundle\EventListener\LocaleListener,
    Symfony\Component\Security\Core\SecurityContext,
    Symfony\Component\Routing\Router,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Routing\RequestContext;


class LocaleListenerTest extends \PHPUnit_Framework_TestCase
{

    public function testOnKernelRequest()
    {
        $session = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Session\SessionInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $session->expects($this->never())
            ->method('set');

        $server = $this->getMock('\Symfony\Component\HttpFoundation\ServerBag');
                $server->expects($this->exactly(2))
                    ->method('get')
                    ->with('HTTP_ACCEPT_LANGUAGE')
                    ->will($this->returnValue('de-DE,de;q=0.8,jp'));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->getMock();
        $parameterBag = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $request->attributes = $parameterBag;
        $request->cookies = $parameterBag;
        $request->server = $server;


        $request
            ->expects($this->never())
            ->method('setDefaultLocale');

        $request
            ->expects($this->once())
            ->method('getLocale');


        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));


        $accessMapStub = $this->getMock('\Symfony\Component\Security\Http\AccessMap');
        $accessMapStub
            ->expects($this->once())
            ->method('getPatterns')
            ->will($this->returnValue(array(array('no valid data'))));
        $securityContextStub = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();
        // request context
        $context = $this->getMockBuilder('Symfony\Component\Routing\RequestContext')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())
            ->method('setParameter')
            ->with('_locale');
        // router
        $router = $this->getMockBuilder('Symfony\Component\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();
        $router->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($context));
        $listener = new LocaleListener($accessMapStub, $securityContextStub, array(array('locale' => 'de')), 'en', $router);
        $listener->onKernelRequest($event);
    }


    public function testOnKernelRequest_WithNoRequest_ShouldDoNothing()
    {

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue(null));


        $accessMapStub = $this->getMock('\Symfony\Component\Security\Http\AccessMap');
        $accessMapStub
            ->expects($this->never())
            ->method('getPatterns')
            ->will($this->returnValue(array(array('hallo'))));
        /** @var SecurityContext $securityContextStub */
        $securityContextStub = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();
        $listener = new LocaleListener($accessMapStub, $securityContextStub, array(array('locale' => 'de')), 'en');
        $voidResult = $listener->onKernelRequest($event);
        $this->assertNull($voidResult);
    }

    public function testOnKernelRequest_WithNoSession_ShouldDoNothing()
    {
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $parameterBag = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $server = $this->getMock('\Symfony\Component\HttpFoundation\ServerBag');
                        $server->expects($this->exactly(2))
                            ->method('get')
                            ->with('HTTP_ACCEPT_LANGUAGE')
                            ->will($this->returnValue('de-DE,de;q=0.8,jp'));
        $request->cookies = $parameterBag;
        $request->server = $server;


        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));


        $accessMapStub = $this->getMock('\Symfony\Component\Security\Http\AccessMap');
        $accessMapStub
            ->expects($this->once())
            ->method('getPatterns')
            ->will($this->returnValue(array(array('hallo'))));
        /** @var SecurityContext $securityContextStub */
        $securityContextStub = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();
        $listener = new LocaleListener($accessMapStub, $securityContextStub, array(array('locale' => 'de')), 'en');
        $voidResult = $listener->onKernelRequest($event);
        $this->assertNull($voidResult);
    }

    public function testOnKernelRequest_WithoutPreviousSession()
    {
        $session = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Session\SessionInterface')
            ->disableOriginalConstructor()
            ->getMock();
//		$session->expects($this->never())
//			->method('get')
//            ->with('_locale')
//			->will($this->returnValue('xy'));

        $server = $this->getMock('\Symfony\Component\HttpFoundation\ServerBag');
        $server->expects($this->exactly(2))
            ->method('get')
            ->with('HTTP_ACCEPT_LANGUAGE')
            ->will($this->returnValue('de-DE,de;q=0.8,jp'));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->getMock();

        $parameterBag = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $request->attributes = $parameterBag;
        $request->server = $server;
        $request->cookies = $parameterBag;

        $request
            ->expects($this->never())
            ->method('setDefaultLocale');


        $request
            ->expects($this->once())
            ->method('getLocale');


        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));


        $accessMapStub = $this->getMock('\Symfony\Component\Security\Http\AccessMap');
        $accessMapStub
            ->expects($this->once())
            ->method('getPatterns')
            ->will($this->returnValue(array(array('no valid data'))));
        $securityContextStub = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();
        // request context
        $context = $this->getMockBuilder('Symfony\Component\Routing\RequestContext')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())
            ->method('setParameter')
            ->with('_locale');
        // router
        $router = $this->getMockBuilder('Symfony\Component\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();
        $router->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($context));
        $listener = new LocaleListener($accessMapStub, $securityContextStub, array(array('locale' => 'de')), 'en', $router);
        $listener->onKernelRequest($event);
    }

    public function testGuessFrontendLocale_withArray()
    {
        $accessMapStub = $this->getMock('\Symfony\Component\Security\Http\AccessMap');
        $accessMapStub
            ->expects($this->never())
            ->method('getPatterns')
            ->will($this->returnValue(array(array('hallo'))));
        /** @var SecurityContext $securityContextStub */
        $securityContextStub = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();
        $listener = new LocaleListener($accessMapStub, $securityContextStub, array(array('locale' => 'en')), 'en');
    }

    public function testGetPreferredLocale_Available_ShouldReturnTheFirstBrowserLanguageThatMatchesAnAvailableLanguage()
    {
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $server = $this->getMock('\Symfony\Component\HttpFoundation\ServerBag');
        $server->expects($this->exactly(2))
            ->method('get')
            ->with('HTTP_ACCEPT_LANGUAGE')
            ->will($this->returnValue('de-DE,de;q=0.8,jp,en-US;q=0.6,en;q=0.4'));
        $request->server = $server;
        $parameterBag = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $request->attributes = $parameterBag;

        $accessMapStub = $this->getMock('\Symfony\Component\Security\Http\AccessMap');
        $accessMapStub
            ->expects($this->never())
            ->method('getPatterns')
            ->will($this->returnValue(array(array('hallo'))));
        /** @var SecurityContext $securityContextStub */
        $securityContextStub = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();
        $listener = new LocaleListener($accessMapStub, $securityContextStub, array(
            array('locale' => 'jp'),
            array('locale' => 'de')
        ), 'en');
        $preferredLocale = $listener->getPreferredLocale($request);
        $this->assertEquals('de', $preferredLocale); // its the first matched browser lang
    }


    public function testGetPreferredLocale_NotAvailable_ShouldReturnDefault()
    {
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $parameterBag = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $request->attributes = $parameterBag;
        $server = $this->getMock('\Symfony\Component\HttpFoundation\ServerBag');
        $server->expects($this->exactly(2))
            ->method('get')
            ->with('HTTP_ACCEPT_LANGUAGE')
            ->will($this->returnValue('de-DE,de;q=0.8,jp'));
        $request->server = $server;

        $accessMapStub = $this->getMock('\Symfony\Component\Security\Http\AccessMap');
        $accessMapStub
            ->expects($this->never())
            ->method('getPatterns')
            ->will($this->returnValue(array(array('hallo'))));
        /** @var SecurityContext $securityContextStub */
        $securityContextStub = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();
        $listener = new LocaleListener($accessMapStub, $securityContextStub, array(
            array('locale' => 'it'),
            array('locale' => 'fr')
        ), 'en');
        $preferredLocale = $listener->getPreferredLocale($request);
        $this->assertEquals('en', $preferredLocale); // its the default
    }
}