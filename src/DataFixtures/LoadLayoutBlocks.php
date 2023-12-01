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
use Networking\InitCmsBundle\Entity\Text;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Networking\InitCmsBundle\Entity\LayoutBlock;
use Networking\InitCmsBundle\Model\TextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class LoadLayoutBlocks.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class LoadLayoutBlocks extends Fixture implements FixtureGroupInterface, OrderedFixtureInterface
{
    public function __construct(
        private readonly array $languages,
        private readonly array $contentTypes,
        private readonly array $templates,
    )
    {
    }
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->languages as $lang) {
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

        foreach ($this->contentTypes as $type) {
            if ( Text::class === $type['class']) {
                $textClass = $type['class'];
                break;
            }
        }
        if (!$textClass) {
            return;
        }

        $text = new $textClass();
        $text->setIsActive(true);
        $text->setSortOrder(1);
        $text->setZone($this->getFirstZone());
        $text->setPage($this->getReference('homepage_'.$locale));
        $text->setText('<h1>Hello World</h1><p>The locale of this page is '.$locale.'</p>');



        $manager->persist($text);
        $manager->flush();
    }

    /**
     * @return array
     */
    protected function getFirstZone(): string
    {
        foreach ($this->templates as $template) {
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
