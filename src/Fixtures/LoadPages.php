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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Networking\InitCmsBundle\Model\PageInterface;

/**
 * Class LoadPages.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class LoadPages extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $languages = $this->container->getParameter('networking_init_cms.page.languages');

        $defaultTemplate = $this->getFirstTemplate();

        if(!$defaultTemplate){
            $defaultTemplate = '@NetworkingInitCms/sandbox/page/one_column.html.twig';
        }

        foreach ($languages as $key => $lang) {
            $this->createHomePages($manager, $defaultTemplate, $lang['locale'], $key, $languages);
        }
    }

    /**
     * @param ObjectManager $manager
     * @param $template
     * @param $locale
     * @param $key
     * @param $languages
     * @throws \Exception
     */
    public function createHomePages(ObjectManager $manager, $template, $locale, $key, $languages)
    {
        $pageClass = $this->container->getParameter('networking_init_cms.admin.page.class');
        /** @var PageInterface $homePage */
        $homePage = new $pageClass();

        $homePage->setLocale($locale);
        $homePage->setPageName('Homepage '.$locale);
        $homePage->setMetaTitle('Homepage '.$locale);
        $homePage->setMetaKeyword('homepage');
        $homePage->setMetaDescription('This is the homepage');
        $homePage->setStatus(PageInterface::STATUS_PUBLISHED);
        $homePage->setIsHome(true);
        $homePage->setTemplateName($template);
        $homePage->setActiveFrom(new \DateTime('now'));

        // set original for translations
        if ($key > 0) {
            $firstPage = $this->getReference('homepage_'.$languages['0']['locale']);
            $homePage->setOriginal($firstPage);
        }

        $manager->persist($homePage);
        $manager->flush();

        $this->addReference('homepage_'.$locale, $homePage);
    }

    /**
     * @return array
     */
    protected function getFirstTemplate()
    {
        $templates = $this->container->getParameter('networking_init_cms.page.templates');

        foreach ($templates as $key => $template) {
            return $key;
        }
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 1;
    }
}
