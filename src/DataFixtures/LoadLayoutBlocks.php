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
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Networking\InitCmsBundle\Entity\LayoutBlock;
use Networking\InitCmsBundle\Model\TextInterface;

/**
 * Class LoadLayoutBlocks.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class LoadLayoutBlocks extends Fixture implements FixtureGroupInterface, OrderedFixtureInterface, ContainerAwareInterface
{
    private ?\Symfony\Component\DependencyInjection\ContainerInterface $container = null;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $languages = $this->container->getParameter('networking_init_cms.page.languages');

        foreach ($languages as $lang) {
            $this->createLayoutBlocks($manager, $lang['locale']);
        }
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     * @param $locale
     */
    public function createLayoutBlocks(ObjectManager $manager, $locale): void
    {
        $textClass = false;

        $contentTypes = $this->container->getParameter('networking_init_cms.page.content_types');
        foreach ($contentTypes as $type) {
            if ($type['name'] == 'Text') {
                $textClass = $type['class'];
                break;
            }
        }
        if (!$textClass) {
            return;
        }

        $layoutBlock = new LayoutBlock();
        $layoutBlock->setIsActive(true);
        $layoutBlock->setSortOrder(1);
        $layoutBlock->setClassType($textClass);
        $layoutBlock->setZone($this->getFirstZone());
        $layoutBlock->setPage($this->getReference('homepage_'.$locale));

        $manager->persist($layoutBlock);
        $manager->flush();

        /** @var TextInterface $text */
        $text = new $textClass();
        $text->setText('<h1>Hello World</h1><p>The locale of this page is '.$locale.'</p>');

        $manager->persist($text);
        $manager->flush();

        $layoutBlock->setObjectId($text->getId());

        $manager->persist($layoutBlock);
        $manager->flush();
    }

    /**
     * @return array
     */
    protected function getFirstZone(): string
    {
        $templates = $this->container->getParameter('networking_init_cms.page.templates');
        foreach ($templates as $template) {
            return $template['zones'][0]['name'];
        }
    }

    public static function getGroups(): array
    {
        return ['init_cms'];
    }

    public function getOrder(): int
    {
        return 2;
    }
}
