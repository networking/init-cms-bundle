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
use \Symfony\Component\HttpFoundation\Request;
use Networking\InitCmsBundle\Entity\BasePage as Page;
use Networking\InitCmsBundle\Entity\Tag;
use Networking\InitCmsBundle\Entity\ContentRoute;

class LanguageSwitcherHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTranslationRoute_WithException()
    {
        $this->setExpectedException('\Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $request = new Request();
        $request2 = Request::create('/foo');

        $router = $this->getMockBuilder('Symfony\Cmf\Component\Routing\DynamicRouter')
            ->disableOriginalConstructor()
            ->getMock();

        $router->expects($this->at(0))
            ->method('matchRequest')
            ->with($request2)
            ->will($this->returnValue(['_content' => new Tag()]));


        $helper = $this->getLanguageHelper($request, $router, $request2);
        $helper->getTranslationRoute('/foo', 'de');
    }

    public function testGetTranslationRoute_WithNoTranslation_ShouldReturnArray()
    {
        $page = new Page();
        $page->setMetaTitle('Page without Translations');

        $request = new Request();
        $request2 = Request::create('/foo');
        $router = $this->getMockBuilder('Symfony\Cmf\Component\Routing\DynamicRouter')
            ->disableOriginalConstructor()
            ->getMock();


        $router->expects($this->once())
            ->method('matchRequest')
            ->with($request2)
            ->will($this->returnValue(['_content' => $page]));

        $helper = $this->getLanguageHelper($request, $router, $request2);
        $result = $helper->getTranslationRoute('/foo', 'de');


        $this->assertEquals(["_route" => "networking_init_cms_home"], $result);
    }

    public function testGetTranslationRoute_PageWithTranslation_ShouldReturnContentRoute()
    {
        $contentRouteDe = new ContentRoute();
        $contentRouteEn = new ContentRoute();

        $dePage = new Page();
        $dePage->setLocale('de');
        $dePage->setMetaTitle('German page with english translation');
        $dePage->setContentRoute($contentRouteDe);

        $enPage = new Page();
        $enPage->setLocale('en');
        $enPage->setMetaTitle('English translation');
        $enPage->setContentRoute($contentRouteEn);
        $dePage->setTranslations([$enPage]);


        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request2 = Request::create('/foo');

        $router = $this->getMockBuilder('Symfony\Cmf\Component\Routing\DynamicRouter')
            ->disableOriginalConstructor()
            ->getMock();

        $router->expects($this->at(0))
            ->method('matchRequest')
            ->with($request2)
            ->will($this->returnValue(['_content' => $dePage]));

        $helper = $this->getLanguageHelper($request, $router, $request2);

        $result = $helper->getTranslationRoute('/foo', 'en');

        $this->assertInstanceOf('Networking\InitCmsBundle\Component\Routing\Route', $result);

    }


    public function testGetQueryString()
    {
        $request = new Request();
        $helper = $this->getLanguageHelper($request, null, $request);

        $qs = $helper->getQueryString();
        $this->assertNull($qs);
    }


    public function testPrepareBaseUrl()
    {
        $serverParams = [
            'SCRIPT_FILENAME' => 'xy.php',
            'SCRIPT_NAME' => 'xy..php',
            'PHP_SELF' => 'xy',
            'ORIG_SCRIPT_NAME' => 'xy.php',
        ];
        $request = new Request([], [], [], [], [], $serverParams);

        $helper = $this->getLanguageHelper($request, null, $request);

        $baseUrl = $helper->prepareBaseUrl('/xy/');
        $this->assertEquals('', $baseUrl);
    }

    public function testGetPathInfo()
    {
        $serverParams = [
            'SCRIPT_FILENAME' => 'xy.php',
            'SCRIPT_NAME' => 'xy..php',
            'PHP_SELF' => 'xy',
            'ORIG_SCRIPT_NAME' => 'xy.php',
        ];

        $request = new Request([], [], [], [], [], $serverParams);

        $helper = $this->getLanguageHelper($request, null, $request);

        $path = $helper->getPathInfo();
        $this->assertEquals('/', $path);
    }

    public function getLanguageHelper(Request $request, $router = null, $request2 = null)
    {

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $pageManager = $this->getMockBuilder('Networking\InitCmsBundle\Entity\PageManager')
            ->disableOriginalConstructor()
            ->getMock();
        $serializer = $this->getMockBuilder('\JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();
        $pageHelper = $this->getMockBuilder('Networking\InitCmsBundle\Helper\PageHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $pageHelper->expects($this->any())
            ->method('matchContentRouteRequest')
            ->willReturn($request2);

        if ($router === null) {

            $router = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\Router')
                ->disableOriginalConstructor()
                ->getMock();
        }
        $helper = new LanguageSwitcherHelper($request, $em, '', $pageHelper);
        $helper->setRouter($router);
        $helper->setPageManager($pageManager);
        $helper->setSerializer($serializer);

        return $helper;
    }
}