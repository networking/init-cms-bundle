<?php
namespace Networking\InitCmsBundle\Tests\Helper;

use \Networking\InitCmsBundle\Helper\LanguageSwitcherHelper;
use \Symfony\Component\DependencyInjection\Container;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Bundle\FrameworkBundle\Routing\Router;

class LanguageSwitcherHelperTest extends \PHPUnit_Framework_TestCase
{

	public $helper = null;

	public function setup()
	{
		$this->helper = new LanguageSwitcherHelper();
	}

	public function testGetTranslationRoute()
	{
        $this->prepareGetTranslationRoute();
        $this->markTestSkipped('prepareGetTranslationRoute ');
        $result = $this->helper->getTranslationRoute('/hell', 'xy');
		$this->assertArrayHasKey('_content', $result);
	}



	public function testGetQueryString()
	{
		$request = new Request();
		$container = $this->getMock('Symfony\Component\DependencyInjection\Container');
		$router = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\Router')
				->disableOriginalConstructor()
				->getMock();
		$container->expects($this->at(0))
			->method('get')
			->will($this->returnValue($request));
		$container->expects($this->at(1))
			->method('get')
			->will($this->returnValue($router));

		$this->helper->setContainer($container);

		$qs = $this->helper->getQueryString();
		$this->assertNull($qs);
//		$this->assertNull('string', $qs);
	}


	public function testPrepareBaseUrl()
	{
		$this->prepareForPrepareBaseUrl();
		$baseUrl = $this->helper->prepareBaseUrl('/xy/');
		$this->assertEquals('', $baseUrl);
	}

	public function testGetPathInfo()
	{
		$this->prepareForPrepareBaseUrl();
		$path = $this->helper->getPathInfo();
		$this->assertEquals('/', $path);
	}

	/* these are protected methods
	public function testGetUrlencodedPrefixFalse()
	{
		$result = $this->helper->geturlencodedPrefix('alskfj', 'lsdkfj');
		$this->assertFalse($result);
	}

	public function testGetUrlencodedPrefixMatch()
	{
		$result = $this->helper->geturlencodedPrefix('alskfj', 'als');
		$this->assertEquals('als', $result);
	}
	public function testGetUrlencodedPrefixMatch2()
	{
		$result = $this->helper->geturlencodedPrefix('%20alskf', ' ');
		$this->assertEquals('%20', $result);
	}
	*/

	private function prepareGetTranslationRoute()
	{
		$request = new Request();
		$container = $this->getMock('Symfony\Component\DependencyInjection\Container');
		$router = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\Router')
				->disableOriginalConstructor()
				->getMock();
		$container->expects($this->at(0))
			->method('get')
			->will($this->returnValue($request));
		$container->expects($this->at(1))
			->method('get')
			->will($this->returnValue($router));

		$router->expects($this->at(0))
			->method('match')
			->with($this->equalTo('/hell'))
			->will($this->returnValue(array('_content' => 'hello')));

		$this->helper->setContainer($container);
	}

	private function prepareForPrepareBaseUrl()
	{
		$serverParams = array(
			'SCRIPT_FILENAME' => 'xy.php',
			'SCRIPT_NAME' => 'xy..php',
			'PHP_SELF' => 'xy',
			'ORIG_SCRIPT_NAME' => 'xy.php',
			'ORIG_SCRIPT_NAME' => 'xy.php',
		);
		$request = new Request(array(), array(), array(), array(), array(), $serverParams);
		$container = $this->getMock('Symfony\Component\DependencyInjection\Container');
		$router = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\Router')
				->disableOriginalConstructor()
				->getMock();
		$container->expects($this->at(0))
			->method('get')
			->will($this->returnValue($request));
		$container->expects($this->at(1))
			->method('get')
			->will($this->returnValue($router));

		$this->helper->setContainer($container);
	}

}