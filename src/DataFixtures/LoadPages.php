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

use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;

/**
 * Class LoadPages.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class LoadPages extends Fixture
    implements FixtureGroupInterface, OrderedFixtureInterface,
               ContainerAwareInterface
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
        $languages = $this->container->getParameter(
            'networking_init_cms.page.languages'
        );

        $defaultTemplate = $this->getFirstTemplate();

        if (!$defaultTemplate) {
            $defaultTemplate
                = '@NetworkingInitCms/sandbox/page/one_column.html.twig';
        }

        foreach ($languages as $key => $lang) {
            $this->createHomePages(
                $manager,
                $defaultTemplate,
                $lang['locale'],
                $key,
                $languages
            );
        }
    }

    /**
     * @param               $template
     * @param               $locale
     * @param               $key
     * @param               $languages
     * @throws \Exception
     */
    public function createHomePages(
        ObjectManager $manager,
        $template,
        $locale,
        $key,
        $languages
    ): void {
        $pageClass = $this->container->getParameter(
            'networking_init_cms.admin.page.class'
        );
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
            $firstPage = $this->getReference(
                'homepage_'.$languages['0']['locale']
            );
            $homePage->getOriginals()->add($firstPage);
        }

        $manager->persist($homePage);
        $manager->flush();

        $this->addReference('homepage_'.$locale, $homePage);
    }


    protected function getFirstTemplate(): string|int
    {
        $templates = $this->container->getParameter(
            'networking_init_cms.page.templates'
        );

        foreach ($templates as $key => $template) {
            return $key;
        }
    }

    public static function getGroups(): array
    {
        return ['init_cms'];
    }

    public function getOrder(): int
    {
        return 1;
    }
}
