<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Tests\Helper;

use \Networking\InitCmsBundle\Helper\LanguageSwitcherHelper;
use \Symfony\Component\DependencyInjection\Container;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Bundle\FrameworkBundle\Routing\Router;
use Networking\InitCmsBundle\Entity\Page;
use Networking\InitCmsBundle\Entity\Tag;

class LanguageSwitcherHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTranslationRouteWithException()
    {
        $this->setExpectedException('\Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $request = new Request();
        $request2 = Request::create('/foo');
        $container = $this->getMock('Symfony\Component\DependencyInjection\Container');
        $router = $this->getMockBuilder('Symfony\Cmf\Component\Routing\DynamicRouter')
				->disableOriginalConstructor()
				->getMock();
        $container->expects($this->at(0))
			->method('get')
            ->with('request')
			->will($this->returnValue($request));
        $container->expects($this->at(1))
			->method('get')
            ->with('router')
			->will($this->returnValue($router));
        $router->expects($this->at(0))
			->method('matchRequest')
			->with($this->equalTo($request2))
			->will($this->returnValue(array('_content' => new Tag())));

        $helper = new LanguageSwitcherHelper();
        $helper->setContainer($container);
        $result = $helper->getTranslationRoute('/foo', 'de');
    }

    public function testGetTranslationRoute()
	{
        $page = new Page();
        $page->setMetaTitle('ExceptionPage');

        $request = new Request();
        $request2 = Request::create('/foo');
		$container = $this->getMock('Symfony\Component\DependencyInjection\Container');
		$router = $this->getMockBuilder('Symfony\Cmf\Component\Routing\DynamicRouter')
				->disableOriginalConstructor()
				->getMock();
		$container->expects($this->at(0))
			->method('get')
            ->with('request')
			->will($this->returnValue($request));
		$container->expects($this->at(1))
			->method('get')
            ->with('router')
			->will($this->returnValue($router));
        $router->expects($this->at(0))
			->method('matchRequest')
			->with($this->equalTo($request2))
			->will($this->returnValue(array('_content' => $page)));

        $helper = new LanguageSwitcherHelper();
		$helper->setContainer($container);
        $result = $helper->getTranslationRoute('/foo', 'de');

        $this->assertEquals(array("_route" => "networking_init_cms_home"), $result);
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

        $helper = new LanguageSwitcherHelper();
		$helper->setContainer($container);

		$qs = $helper->getQueryString();
		$this->assertNull($qs);
//		$this->assertNull('string', $qs);
	}


	public function testPrepareBaseUrl()
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

        $helper = new LanguageSwitcherHelper();
		$helper->setContainer($container);

		$baseUrl = $helper->prepareBaseUrl('/xy/');
		$this->assertEquals('', $baseUrl);
	}

	public function testGetPathInfo()
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

        $helper = new LanguageSwitcherHelper();
		$helper->setContainer($container);

		$path = $helper->getPathInfo();
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
}