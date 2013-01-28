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
use Networking\InitCmsBundle\Entity\HelpText;


class LoadHelpText extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    private $container;

    /**
     * @var array
     */
    private $textArray = array(
        'en_US' => array(
            'not_found' => array(
                'title' => 'Not Found ',
                'text' => 'No Help Text found for this item',
                'is_deletable' => '0'
            ),
            'dashboard' => array(
                'title' => 'Dashboard',
                'text' => 'Dashboard Text',
                'is_deletable' => '1'
            ),
            'networking_init_cms.menu.admin.menu_item.edit' => array(
                'title' => 'Menu Item Edit',
                'text' => 'Menu Item Edit Help Text',
                'is_deletable' => '1'
            ),
            'networking_init_cms.page.admin.page.edit' => array(
                'title' => 'Page Edit',
                'text' => 'Page edit help text',
                'is_deletable' => '1'
            ),
            'networking_init_cms.page.admin.page.list' => array(
                'title' => 'Page List',
                'text' => 'Page List help text',
                'is_deletable' => '1'
            ),
            'networking_init_cms.menu.admin.menu_item.navigation' => array(
                'title' => 'Menu Item Navigation',
                'text' => 'Menu Item Navigation Help Text',
                'is_deletable' => '1'
            )
        ),
        'de_CH' => array(
            'not_found' => array(
                'title' => 'nicht gefunden ',
                'text' => 'Kein Hilfe Text',
                'is_deletable' => '0'
            ),
            'dashboard' => array(
                'title' => 'Dashboard',
                'text' => 'Dashboard Text',
                'is_deletable' => '1'
            ),
            'networking_init_cms.menu.admin.menu_item.edit' => array(
                'title' => 'Menu Bearbeiten',
                'text' => 'Menu bearbeiten hilfe text',
                'is_deletable' => '1'
            ),
            'networking_init_cms.page.admin.page.edit' => array(
                'title' => 'Page Edit',
                'text' => 'Page edit help text',
                'is_deletable' => '1'
            ),
            'networking_init_cms.page.admin.page.list' => array(
                'title' => 'Page List',
                'text' => 'Page List help text',
                'is_deletable' => '1'
            ),
            'networking_init_cms.menu.admin.menu_item.navigation' => array(
                'title' => 'Menu Item Navigation',
                'text' => 'Menu Item Navigation Help Text',
                'is_deletable' => '1'
            )

        )
    );


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
        foreach ($this->textArray as $locale => $value) {
            foreach ($value as $translationKey => $row) {

                $this->createHelpText(
                    $manager,
                    $locale,
                    $translationKey,
                    $row['title'],
                    $row['text'],
                    $row['is_deletable']
                );
            }
            //unset($translator);

        }
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     * @param $locale
     * @param $translationKey
     * @param $title
     * @param $text
     */
    public function createHelpText(ObjectManager $manager, $locale, $translationKey, $title, $text, $isDeletable)
    {
        $helpText = new HelpText();
        $helpText->setTitle($title);
        $helpText->setText($text);
        $helpText->setLocale($locale);
        $helpText->setTranslationKey($translationKey);
        $helpText->setIsDeletable($isDeletable);

        $manager->persist($helpText);
        $manager->flush();
    }


    /**
     * @return int
     */
    public function getOrder()
    {
        return 5;
    }
}
