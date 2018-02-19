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
use Networking\InitCmsBundle\Entity\LayoutBlock;
use Networking\InitCmsBundle\Model\TextInterface;

/**
 * Class LoadLayoutBlocks
 * @package Networking\InitCmsBundle\Fixtures
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class LoadLayoutBlocks extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
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
    public function createLayoutBlocks(ObjectManager $manager, $locale)
    {
        $textClass = false;

        $contentTypes = $this->container->getParameter('networking_init_cms.page.content_types');
        foreach($contentTypes as $type){
            if($type['name'] == 'Text'){
                $textClass = $type['class'];
                break;
            }
        }
        if(!$textClass){
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

        /** @var TextInterface  $text */
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
    protected function getFirstZone()
    {
        $templates = $this->container->getParameter('networking_init_cms.page.templates');
        foreach ($templates as $template) {
            return $template['zones'][0]['name'];
        }
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 2;
    }
}
