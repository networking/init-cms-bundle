<?php
namespace Networking\InitCmsBundle\Tests\EventListener;

use Networking\InitCmsBundle\EventListener\LayoutBlockFormListener;
use Symfony\Component\Form\FormFactoryInterface;

class LayoutBlockFormListenerTest extends \PHPUnit_Framework_TestCase
{


	public function testPreBindData()
	{
		$this->markTestIncomplete(
		    'Call to undefined method Networking\InitCmsBundle\EventListener\LayoutBlockFormListener::getForm() ???'
	    );
		$factory = $this->getMockBuilder('\Symfony\Component\Form\FormFactoryInterface')
				->disableOriginalConstructor()
				->getMock();
		$container = $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerInterface')
				->disableOriginalConstructor()
				->getMock();
		$listener = new LayoutBlockFormListener($factory, $container);
		$form = $listener->getForm();

		$event = $this->getMockBuilder('\Symfony\Component\Form\FormEvent')
				->disableOriginalConstructor()
				->getMock();
		$event->expects($this->once())
			->method('getData')
			->will($this->returnValue(array('someKey' => 'someData')));
		$event->expects($this->exactly(2))
			->method('getForm')
			->will($this->returnValue($form));
		$listener->preBindData($event);
	}

}