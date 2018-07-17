<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Fixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Networking\InitCmsBundle\Entity\MenuItem;

/**
 * Class LoadMenu.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class LoadMenu extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $languages = $this->container->getParameter('networking_init_cms.page.languages');

        foreach ($languages as $lang) {
            $this->createMenuItems($manager, $lang['locale']);
        }
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     * @param $locale
     */
    public function createMenuItems(ObjectManager $manager, $locale)
    {
        $menuRoot = new MenuItem();

        $menuRoot->setName('Main menu');
        $menuRoot->setLocale($locale);
        $menuRoot->setIsRoot(true);
        $menuRoot->setLvl(1);
        $menuRoot->setLft(1);
        $menuRoot->setRgt(2);

        $manager->persist($menuRoot);
        $manager->flush();

        $homePageMenu = new MenuItem();
        $homePageMenu->setName('Homepage');
        $homePageMenu->setPage($this->getReference('homepage_'.$locale));
        $homePageMenu->setParent($menuRoot);

        $manager->persist($homePageMenu);
        $manager->flush();

        $footerRoot = new MenuItem();
        $footerRoot->setName('Footer menu');
        $footerRoot->setLocale($locale);
        $footerRoot->setIsRoot(true);
        $footerRoot->setLvl(1);
        $footerRoot->setLft(1);
        $footerRoot->setRgt(2);

        $manager->persist($footerRoot);
        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 3;
    }
}
