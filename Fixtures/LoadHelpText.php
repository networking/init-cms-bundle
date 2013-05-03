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

        'overview' => array(
            'title' => 'overview.title',
            'text' => 'overview.text',
            'is_deletable' => '1'
        ),
        'dashboard' => array(
            'title' => 'dashboard.title',
            'text' => 'dashboard.text',
            'is_deletable' => '1'
        ),
        //pages
        'networking_init_cms.page.admin.page.list' => array(
            'title' => 'networking_init_cms.page.admin.page.list.title',
            'text' => 'networking_init_cms.page.admin.page.list.text',
            'is_deletable' => '1'
        ),
        'networking_init_cms.page.admin.page.create' => array(
            'title' => 'networking_init_cms.page.admin.page.create.title',
            'text' => 'networking_init_cms.page.admin.page.create.text',
            'is_deletable' => '1'
        ),
        'networking_init_cms.page.admin.page.edit' => array(
            'title' => 'networking_init_cms.page.admin.page.edit.title',
            'text' => 'networking_init_cms.page.admin.page.edit.text',
            'is_deletable' => '1'
        ),
        //menu
        'networking_init_cms.menu.admin.menu_item.navigation' => array(
            'title' => 'networking_init_cms.menu.admin.menu_item.navigation.title',
            'text' => 'networking_init_cms.menu.admin.menu_item.navigation.text',
            'is_deletable' => '1'
        ),
        'networking_init_cms.menu.admin.menu.create' => array(
            'title' => 'networking_init_cms.menu.admin.menu.create.title',
            'text' => 'networking_init_cms.menu.admin.menu.create.text',
            'is_deletable' => '1'
        ),
        'networking_init_cms.menu.admin.menu_item.create' => array(
            'title' => 'networking_init_cms.menu.admin.menu_item.create.title',
            'text' => 'networking_init_cms.menu.admin.menu_item.create.text',
            'is_deletable' => '1'
        ),
        //media
        'sonata.media.admin.media.list' => array(
            'title' => 'sonata.media.admin.media.list.title',
            'text' => 'sonata.media.admin.media.list.text',
            'is_deletable' => '1'
        ),
        'sonata.media.admin.gallery.list' => array(
            'title' => 'sonata.media.admin.gallery.list.title',
            'text' => 'sonata.media.admin.gallery.list.text',
            'is_deletable' => '1'
        ),
        'networking_init_cms.page.admin.tag.list' => array(
            'title' => 'networking_init_cms.page.admin.tag.list.title',
            'text' => 'networking_init_cms.page.admin.tag.list.text',
            'is_deletable' => '1'
        ),
        //user
        'sonata.user.admin.user.list' => array(
            'title' => 'sonata.user.admin.user.list.title',
            'text' => 'sonata.user.admin.user.list.text',
            'is_deletable' => '1'
        ),
        'sonata.user.admin.group.list' => array(
            'title' => 'sonata.user.admin.group.list.title',
            'text' => 'sonata.user.admin.group.list.text',
            'is_deletable' => '1'
        ),
        //not found
        'not_found' => array(
            'title' => 'not_found.title',
            'text' => 'not_found.text',
            'is_deletable' => '0'
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
        $languages = $this->container->getParameter('networking_init_cms.page.languages');
        foreach ($languages as $lang) {
            $this->container->get('translator')->setLocale($lang['locale']);
            foreach ($this->textArray as $translationKey => $row) {

                $this->createHelpText(
                    $manager,
                    $lang['locale'],
                    $translationKey,
                    $this->container->get('translator')->trans($row['title'], array(), 'HelpTextAdmin'),
                    $this->container->get('translator')->trans($row['text'], array(), 'HelpTextAdmin'),
                    $row['is_deletable']
                );
            }
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
