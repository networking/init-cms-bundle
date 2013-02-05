<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
