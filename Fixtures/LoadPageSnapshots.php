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

use Networking\InitCmsBundle\Entity\Page,
    Networking\InitCmsBundle\Entity\ContentRoute,
    Networking\InitCmsBundle\Helper\PageHelper,
    Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Doctrine\Common\Persistence\ObjectManager;

/**
 * @author sonja brodersen <s.brodersen@networking.ch>
 */

class LoadPageSnapshots extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    private $container;


    /**
     * Sets the Container.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    function load(ObjectManager $manager)
    {

        $languages = $this->container->getParameter('networking_init_cms.page.languages');

        foreach ($languages as $lang) {
            $this->createPageSnapshots($lang['locale']);
        }
    }

    /**
     * create a snapshot for each page
     *
     * @param $locale
     *
     * @TODO the first snapshot is a proxy! do something!
     */
    private function createPageSnapshots($locale)
    {
        // get the created page
        $page = $this->getReference('homepage_'.$locale);

        $pageHelper = $this->container->get('networking_init_cms.helper.page_helper');
        $pageHelper->makePageSnapshot($page);
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    function getOrder()
    {
        return 4;
    }

}
