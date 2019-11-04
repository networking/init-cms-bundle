<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\tests\EventListener;

use PHPUnit\Framework\TestCase;
use Networking\InitCmsBundle\EventListener\LocaleListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class LocaleListenerTest extends TestCase
{
    public function testOnKernelRequest()
    {
        $session = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Session\SessionInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $session->expects($this->never())
            ->method('set');

        $server = $this->createMock('\Symfony\Component\HttpFoundation\ServerBag');

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
        $request->query = $parameterBag;

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

        $event->expects($this->once())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        $accessMapStub = $this->createMock('\Symfony\Component\Security\Http\AccessMap');
        $accessMapStub
            ->expects($this->once())
            ->method('getPatterns')
            ->will($this->returnValue([['no valid data']]));
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
        $listener = new LocaleListener($accessMapStub, [['locale' => 'de']], true, false, 'en', $router);
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

        $accessMapStub = $this->createMock('\Symfony\Component\Security\Http\AccessMap');
        $accessMapStub
            ->expects($this->never())
            ->method('getPatterns')
            ->will($this->returnValue([['hallo']]));
        $listener = new LocaleListener($accessMapStub, [['locale' => 'de']], true, false,'en');
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
        $server = $this->createMock('\Symfony\Component\HttpFoundation\ServerBag');
        $server->expects($this->exactly(2))
                            ->method('get')
                            ->with('HTTP_ACCEPT_LANGUAGE')
                            ->will($this->returnValue('de-DE,de;q=0.8,jp'));
        $request->cookies = $parameterBag;
        $request->server = $server;
        $request->query = $parameterBag;

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $event->expects($this->once())
              ->method('getRequestType')
              ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        $accessMapStub = $this->createMock('\Symfony\Component\Security\Http\AccessMap');
        $accessMapStub
            ->expects($this->once())
            ->method('getPatterns')
            ->will($this->returnValue([['hallo']]));
        $listener = new LocaleListener($accessMapStub, [['locale' => 'de']],true, false, 'en');
        $voidResult = $listener->onKernelRequest($event);
        $this->assertNull($voidResult);
    }

    public function testOnKernelRequest_WithoutPreviousSession()
    {
        $server = $this->createMock('\Symfony\Component\HttpFoundation\ServerBag');
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
	    $request->query = $parameterBag;

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

        $event->expects($this->once())
              ->method('getRequestType')
              ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        $accessMapStub = $this->createMock('\Symfony\Component\Security\Http\AccessMap');
        $accessMapStub
            ->expects($this->once())
            ->method('getPatterns')
            ->will($this->returnValue([['no valid data']]));
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
        $listener = new LocaleListener($accessMapStub, [['locale' => 'de']],true, false, 'en', $router);
        $listener->onKernelRequest($event);
    }

    public function testGuessFrontendLocale_withArray()
    {
        $accessMapStub = $this->createMock('\Symfony\Component\Security\Http\AccessMap');
        $accessMapStub
            ->expects($this->never())
            ->method('getPatterns')
            ->will($this->returnValue([['hallo']]));
        $listener = new LocaleListener($accessMapStub, [['locale' => 'en']],true, false, 'en');
    }

    public function testGetPreferredLocale_Available_ShouldReturnTheFirstBrowserLanguageThatMatchesAnAvailableLanguage()
    {
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $server = $this->createMock('\Symfony\Component\HttpFoundation\ServerBag');
        $server->expects($this->exactly(2))
            ->method('get')
            ->with('HTTP_ACCEPT_LANGUAGE')
            ->will($this->returnValue('de-DE,de;q=0.8,jp,en-US;q=0.6,en;q=0.4'));
        $request->server = $server;
        $parameterBag = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $request->attributes = $parameterBag;

        $accessMapStub = $this->createMock('\Symfony\Component\Security\Http\AccessMap');
        $accessMapStub
            ->expects($this->never())
            ->method('getPatterns')
            ->will($this->returnValue([['hallo']]));
        $listener = new LocaleListener($accessMapStub, [
            ['locale' => 'jp'],
            ['locale' => 'de'],
        ],true, false, 'en');
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
        $server = $this->createMock('\Symfony\Component\HttpFoundation\ServerBag');
        $server->expects($this->exactly(2))
            ->method('get')
            ->with('HTTP_ACCEPT_LANGUAGE')
            ->will($this->returnValue('de-DE,de;q=0.8,jp'));
        $request->server = $server;

        $accessMapStub = $this->createMock('\Symfony\Component\Security\Http\AccessMap');
        $accessMapStub
            ->expects($this->never())
            ->method('getPatterns')
            ->will($this->returnValue([['hallo']]));
        $listener = new LocaleListener($accessMapStub, [
            ['locale' => 'it'],
            ['locale' => 'fr'],
        ],true, false, 'en');
        $preferredLocale = $listener->getPreferredLocale($request);
        $this->assertEquals('en', $preferredLocale); // its the default
    }
}
