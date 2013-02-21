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

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    private $config;

    public function __construct($config)
    {
        parent::__construct('test', true);

        $fs = new Filesystem();
        if (!$fs->isAbsolutePath($config)) {
            $config = __DIR__.'/config/'.$config;
        }

        if (!file_exists($config)) {
            throw new \RuntimeException(sprintf('The config file "%s" does not exist.', $config));
        }

        $this->config = $config;
    }

    public function registerBundles()
    {
        return array(
//	        new \Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
	        new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
	        new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
	        new \Symfony\Bundle\TwigBundle\TwigBundle(),
	        new \Mopa\Bundle\BootstrapBundle\MopaBootstrapBundle(),
	        new \Sonata\AdminBundle\SonataAdminBundle(),
	        new \Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
	        new \Networking\InitCmsBundle\NetworkingInitCmsBundle(),
            new \Sonata\UserBundle\SonataUserBundle('FOSUserBundle'),
            new \FOS\UserBundle\FOSUserBundle(),
            new \Networking\InitCmsBundle\Tests\Functional\TestBundle\TestBundle(),
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->config);
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/NetworkingInitCmsBundle';
    }
}
