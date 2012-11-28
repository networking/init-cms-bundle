<?php
namespace Networking\InitCmsBundle\Tests\Controller;

use Networking\InitCmsBundle\Controller\DefaultController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultControllerTest extends \PHPUnit_Framework_TestCase
{

	/**
	 *
	 */
	public function testIndexAction()
	{
		$request = new Request(array(), array(), array(), array(), array(), array(), 'bla');
		$request->setLocale('de_CH');
		$controller = new DefaultController();
		$response = $controller->indexAction($request);
		$this->assertInstanceOf('Networking\InitCmsBundle\Controller\DefaultController', $controller, 'Controller ist a DefaultController');
		$this->assertInternalType('array', $response, 'response is an array');
		$this->assertEquals(array('page'=>null), $response, 'response is an array');
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
	 * :( i have to mock the tested object
  	 */
	public function testHomeAction()
	{
	    $this->markTestIncomplete(
		    'This test has not been implemented yet.'
	    );

		$mockPage = new \Networking\InitCmsBundle\Entity\Page;
		$mockRepo = $this->getMock('\Doctrine\ORM\Repository', array('findOneBy'));
		$mockDoctrine = $this->getMock('\Doctrine\ORM\Repository', array('getRepository'));
		$controller = $this->getMock('\Networking\InitCmsBundle\Controller\DefaultController', array('getDoctrine', 'getRequest', 'homeAction'));
		$controller->expects($this->once())
				->method('homeAction')
				->will($this->returnValue(array('page'=>$mockPage)));
		// it never gets called, because we mock it!
//		$mockDoctrine->expects($this->once())
//				->method('getRepository')
//				->with($this->equalTo('NetworkingInitCmsBundle:Page'))
//				->will($this->returnValue($mockRepo));
//		$controller->expects($this->once())
//				->method('getDoctrine')
//				->will($this->returnValue($mockDoctrine));
//		$mockRepo->expects($this->once())
//				->method('findOneBy')
//				->with($this->equalTo(array('isHome' => true, 'locale' =>'en')))
//				->will($this->returnValue($mockPage));
		$response = $controller->homeAction();
		$this->assertInternalType('array', $response, 'response is an array');
	}


	public function testAdminAction()
	{
		$this->markTestIncomplete(
		    'This test has not been implemented yet.'
	    );
		$controller = $this->getMock('\Networking\InitCmsBundle\Controller\DefaultController', array('adminAction', 'generateUrl', 'redirect'));
		$this->assertInstanceOf('Networking\InitCmsBundle\Controller\DefaultController', $controller, 'Controller ist a DefaultController');

		$controller->expects($this->once())
			->method('adminAction')
			->will($this->returnValue('url'));
		// it never gets called, because we mock it!
//		$controller->expects($this->once())
//			->method('generateUrl')
//			->with($this->equalTo('sonata_admin_dashboard'))
//			->will($this->returnValue('url'));
//		$controller->expects($this->once())
//			->method('redirect')
//			->with($this->equalTo('url'))
//			->will($this->returnValue('response'));
		$response = $controller->adminAction();
		$this->assertEquals($response, 'url', 'all just mocked up!');
	}
}
