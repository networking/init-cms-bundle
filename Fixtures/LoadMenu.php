<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yorkie
 * Date: 01.11.12
 * Time: 16:26
 * To change this template use File | Settings | File Templates.
 */
namespace Networking\InitCmsBundle\Fixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Networking\InitCmsBundle\Entity\MenuItem;

class LoadMenu extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    private $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

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

        $menuRoot->setName('#main#');
        $menuRoot->setLocale($locale);
        $menuRoot->setLvl(1);
        $menuRoot->setLft(1);
        $menuRoot->setRgt(2);

        $manager->persist($menuRoot);
        $manager->flush();

        $homePageMenu = new MenuItem();
        $homePageMenu->setName('Homepage');
        $homePageMenu->setPage($this->getReference('homepage_' . $locale));
        $homePageMenu->setParent($menuRoot);

        $manager->persist($homePageMenu);
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
