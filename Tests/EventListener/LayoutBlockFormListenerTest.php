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

use Networking\InitCmsBundle\EventListener\LayoutBlockFormListener;
use Symfony\Component\Form\FormFactoryInterface;

class LayoutBlockFormListenerTest extends \PHPUnit_Framework_TestCase
{

	public function testPreBindData()
	{
		$factory = $this->getMockBuilder('\Symfony\Component\Form\FormFactoryInterface')
				->disableOriginalConstructor()
				->getMock();
		$container = $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerInterface')
				->disableOriginalConstructor()
				->getMock();
		$listener = new LayoutBlockFormListener($factory, $container);
        $form = new \Object();

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