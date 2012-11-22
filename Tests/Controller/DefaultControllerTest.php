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
		var_dump($request->get('_content'));
		var_dump($request->getContent());
		$controller = new DefaultController();
		$response = $controller->indexAction($request);
		$this->assertInstanceOf('Networking\InitCmsBundle\Controller\DefaultController', $controller, 'Controller ist a DefaultController');
		$this->assertInternalType('array', $response, 'response is an array');
		$this->assertEquals(array('page'=>'bla'), $response, 'response is an array');
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
	 *
	 */
	public function testHomeAction()
	{
		$request = new Request();
		$request->setLocale('de_CH');
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Request', $request, 'request class');
		$this->assertEquals('de_CH', $request->getLocale(), 'request locale');
		$controller = new DefaultController();

		// Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );

		$response = $controller->homeAction(); // this causes phpunit to crash ???
		$this->assertInternalType('array', $response, 'response is an array');
	}
}
