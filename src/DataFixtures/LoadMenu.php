<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Networking\InitCmsBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Networking\InitCmsBundle\Entity\MenuItem;

/**
 * Class LoadMenu.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class LoadMenu extends Fixture
    implements FixtureGroupInterface, OrderedFixtureInterface
{

    public function __construct(
        protected array $languages
    )
    {

    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {

        foreach ($this->languages as $lang) {
            $this->createMenuItems($manager, $lang['locale']);
        }
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     * @param                                            $locale
     */
    public function createMenuItems(ObjectManager $manager, $locale): void
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

    public static function getGroups(): array
    {
        return ['init_cms'];
    }

    public function getOrder(): int
    {
        return 3;
    }
}
