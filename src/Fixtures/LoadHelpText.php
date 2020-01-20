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
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Networking\InitCmsBundle\Entity\HelpText;

/**
 * Class LoadHelpText.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class LoadHelpText extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $textArray = [

        'overview' => [
            'title' => 'overview.title',
            'text' => 'overview.text',
            'is_deletable' => '1',
        ],
        'dashboard' => [
            'title' => 'dashboard.title',
            'text' => 'dashboard.text',
            'is_deletable' => '1',
        ],
        //pages
        'networking_init_cms.admin.page.list' => [
            'title' => 'networking_init_cms.admin.page.list.title',
            'text' => 'networking_init_cms.admin.page.list.text',
            'is_deletable' => '1',
        ],
        'networking_init_cms.admin.page.create' => [
            'title' => 'networking_init_cms.admin.page.create.title',
            'text' => 'networking_init_cms.admin.page.create.text',
            'is_deletable' => '1',
        ],
        'networking_init_cms.admin.page.edit' => [
            'title' => 'networking_init_cms.admin.page.edit.title',
            'text' => 'networking_init_cms.admin.page.edit.text',
            'is_deletable' => '1',
        ],
        //menu
        'networking_init_cms.admin.menu_item.navigation' => [
            'title' => 'networking_init_cms.admin.menu_item.navigation.title',
            'text' => 'networking_init_cms.admin.menu_item.navigation.text',
            'is_deletable' => '1',
        ],
        'networking_init_cms.admin.menu.create' => [
            'title' => 'networking_init_cms.admin.menu.create.title',
            'text' => 'networking_init_cms.admin.menu.create.text',
            'is_deletable' => '1',
        ],
        'networking_init_cms.admin.menu_item.create' => [
            'title' => 'networking_init_cms.admin.menu_item.create.title',
            'text' => 'networking_init_cms.admin.menu_item.create.text',
            'is_deletable' => '1',
        ],
        'networking_init_cms.admin.menu_item.edit' => [
            'title' => 'networking_init_cms.admin.menu_item.edit.title',
            'text' => 'networking_init_cms.admin.menu_item.edit.text',
            'is_deletable' => '1',
        ],
        //media
        'sonata.media.admin.media.list' => [
            'title' => 'sonata.media.admin.media.list.title',
            'text' => 'sonata.media.admin.media.list.text',
            'is_deletable' => '1',
        ],
        'sonata.media.admin.media.create' => [
            'title' => 'sonata.media.admin.media.create.title',
            'text' => 'sonata.media.admin.media.create.text',
            'is_deletable' => '1',
        ],
        'sonata.media.admin.media.edit' => [
            'title' => 'sonata.media.admin.media.edit.title',
            'text' => 'sonata.media.admin.media.edit.text',
            'is_deletable' => '1',
        ],
        //gallery
        'sonata.media.admin.gallery.list' => [
            'title' => 'sonata.media.admin.gallery.list.title',
            'text' => 'sonata.media.admin.gallery.list.text',
            'is_deletable' => '1',
        ],
        'sonata.media.admin.gallery.create' => [
            'title' => 'sonata.media.admin.gallery.create.title',
            'text' => 'sonata.media.admin.gallery.create.text',
            'is_deletable' => '1',
        ],
        'sonata.media.admin.gallery.edit' => [
            'title' => 'sonata.media.admin.gallery.edit.title',
            'text' => 'sonata.media.admin.gallery.edit.text',
            'is_deletable' => '1',
        ],
        //user
        'sonata.user.admin.user.list' => [
            'title' => 'sonata.user.admin.user.list.title',
            'text' => 'sonata.user.admin.user.list.text',
            'is_deletable' => '1',
        ],
        'sonata.user.admin.user.create' => [
            'title' => 'sonata.user.admin.user.create.title',
            'text' => 'sonata.user.admin.user.create.text',
            'is_deletable' => '1',
        ],
        'sonata.user.admin.user.edit' => [
            'title' => 'sonata.user.admin.user.edit.title',
            'text' => 'sonata.user.admin.user.edit.text',
            'is_deletable' => '1',
        ],
        //groups
        'sonata.user.admin.group.list' => [
            'title' => 'sonata.user.admin.group.list.title',
            'text' => 'sonata.user.admin.group.list.text',
            'is_deletable' => '1',
        ],
        'sonata.user.admin.group.create' => [
            'title' => 'sonata.user.admin.group.create.title',
            'text' => 'sonata.user.admin.group.create.text',
            'is_deletable' => '1',
        ],
        'sonata.user.admin.group.edit' => [
            'title' => 'sonata.user.admin.group.edit.title',
            'text' => 'sonata.user.admin.group.edit.text',
            'is_deletable' => '1',
        ],
        //media tags
        'networking_init_cms.admin.tag.list' => [
            'title' => 'networking_init_cms.admin.tag.list.title',
            'text' => 'networking_init_cms.admin.tag.list.text',
            'is_deletable' => '1',
        ],
        'networking_init_cms.admin.tag.create' => [
            'title' => 'networking_init_cms.admin.tag.create.title',
            'text' => 'networking_init_cms.admin.tag.create.text',
            'is_deletable' => '1',
        ],
        'networking_init_cms.admin.tag.edit' => [
            'title' => 'networking_init_cms.admin.tag.edit.title',
            'text' => 'networking_init_cms.admin.tag.edit.text',
            'is_deletable' => '1',
        ],
        //not found
        'not_found' => [
            'title' => 'not_found.title',
            'text' => 'not_found.text',
            'is_deletable' => '0',
        ],
    ];

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
                    $this->container->get('translator')->trans($row['title'], [], 'HelpTextAdmin'),
                    $this->container->get('translator')->trans($row['text'], [], 'HelpTextAdmin'),
                    $row['is_deletable']
                );
            }
        }
    }

    /**
     * @param ObjectManager $manager
     * @param $locale
     * @param $translationKey
     * @param $title
     * @param $text
     * @param $isDeletable
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
