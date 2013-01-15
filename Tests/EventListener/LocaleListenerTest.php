<?php
namespace Networking\InitCmsBundle\Tests\EventListener;

use Networking\InitCmsBundle\EventListener\LocaleListener,
    Symfony\Component\Security\Core\SecurityContext;


class LocaleListenerTest extends \PHPUnit_Framework_TestCase
{

	protected function getResponseEventMock($AttributeReturnValue, $SessionReturnValue)
    {

	    $session = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Session\SessionInterface')
				->disableOriginalConstructor()
				->getMock();
		$session->expects($this->once())
			->method('get')
			->will($this->returnValue($SessionReturnValue));
		$session->expects($this->once())
			->method('set');

	    $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
	    $request
			    ->expects($this->exactly(2))
			    ->method('hasPreviousSession')
	            ->will($this->returnValue(TRUE));
	    $request
			    ->expects($this->once())
			    ->method('setDefaultLocale');
	    $request
			    ->expects($this->once())
			    ->method('setLocale');

		$request->attributes = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag')
				->disableOriginalConstructor()
				->getMock();
		$request->attributes->expects($this->once())
			->method('get')
			->will($this->returnValue($AttributeReturnValue));

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

		return $event;
    }


	public function testOnKernelRequest()
	{
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
//        $listener->setAccessMap($accessMapStub);
        $listener->onKernelRequest($this->getResponseEventMock('de', 'xy'));
    }

}