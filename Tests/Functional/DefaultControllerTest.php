<?php

namespace Networking\InitCmsBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

error_reporting(E_ALL);

class DefaultControllerTest extends WebTestCase
{

	/**
	 * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
	 */
	public function testHomepage()
    {
//	    // -------- Stop here and mark this test as incomplete.
//	    $this->markTestIncomplete(
//		    'This test has not been implemented yet.'
//	    );

	    $client = $this->createClient();
        $router = self::$kernel->getContainer()->get('router');

        $crawler = $client->request('GET', $router->generate('homepage'));
        $this->assertTrue($crawler->filter('html:contains("hello")')->count() > 0);
    }

    protected function setUp()
    {
        $fs = new Filesystem();
        $fs->remove(sys_get_temp_dir().'/NetworkingInitCmsBundle/');
    }

    protected static function createKernel(array $options = array())
    {
        return self::$kernel = new AppKernel(
            isset($options['config']) ? $options['config'] : 'default.yml'
        );
    }

}
