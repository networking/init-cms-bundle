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
    Symfony\Component\Security\Core\SecurityContext;


class LocaleListenerTest extends \PHPUnit_Framework_TestCase
{

    /*
     * ???
     */
	public function testOnKernelRequest()
	{
        $session = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Session\SessionInterface')
				->disableOriginalConstructor()
				->getMock();
		$session->expects($this->once())
			->method('get')
			->will($this->returnValue('xy'));
		$session->expects($this->any())
			->method('set');

	    $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
	    $request
			    ->expects($this->any())
			    ->method('hasPreviousSession')
	            ->will($this->returnValue(TRUE));
	    $request
			    ->expects($this->any())
			    ->method('setDefaultLocale');
	    $request
			    ->expects($this->once())
			    ->method('setLocale');

		$request->attributes = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag')
				->disableOriginalConstructor()
				->getMock();
		$request->attributes->expects($this->any())
			->method('get')
			->will($this->returnValue('de'));

	    $request
			->expects($this->any())
		    ->method('getSession')
		    ->will($this->returnValue($session));

	    $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));


        $accessMapStub = $this->getMock('\Symfony\Component\Security\Http\AccessMap');
		$accessMapStub
                ->expects($this->once())
                ->method('getPatterns')
                ->will($this->returnValue(array(array('hallo'))));
        /** @var SecurityContext $securityContextStub  */
        $securityContextStub = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
                ->disableOriginalConstructor()
				->getMock();
        $listener = new LocaleListener($accessMapStub, $securityContextStub, array(array('locale'=>'en')), 'en');
        $listener->onKernelRequest($event);
    }

}