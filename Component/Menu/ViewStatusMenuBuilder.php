<?php
/*
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Component\Menu;

use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface,
    Networking\InitCmsBundle\Doctrine\Extensions\Versionable\ResourceVersionInterface,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Security\Core\SecurityContextInterface,
    Symfony\Component\DependencyInjection\Container,
    Mopa\Bundle\BootstrapBundle\Navbar\AbstractNavbarMenuBuilder,
    Knp\Menu\FactoryInterface,
    Knp\Menu\Iterator\RecursiveItemIterator;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class ViewStatusMenuBuilder extends AbstractNavbarMenuBuilder
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $securityContext;
    /**
     * @var bool
     */
    protected $isLoggedIn;
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $serviceContainer;
    /**
     * @var \Symfony\Cmf\Component\Routing\ChainRouter
     */
    protected $router;

    /**
     * @param \Knp\Menu\FactoryInterface                                $factory
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     * @param \Symfony\Component\DependencyInjection\Container          $serviceContainer
     */
    public function __construct(
        FactoryInterface $factory,
        SecurityContextInterface $securityContext,
        Container $serviceContainer
    ) {
        parent::__construct($factory);

        $this->securityContext = $securityContext;
        if ($this->securityContext->isGranted('IS_AUTHENTICATED_FULLY') || $this->securityContext->isGranted(
            'IS_AUTHENTICATED_REMEMBERED'
        )
        ) {
            var_dump('hello');
            $this->isLoggedIn = true;
        }
        $this->serviceContainer = $serviceContainer;
        $this->router = $this->serviceContainer->get('router');
    }

    public function createViewStatusMenu(Request $request)
    {
        // Default to homepage
        $liveRoute = $draftRoute = $this->serviceContainer->get('router')->generate('networking_init_cms_default');

        $menu = $this->factory->createItem('root');

        $entity = null;

        $editUrl = null;

        if ($this->isLoggedIn) {
            $menu->setChildrenAttribute('class', 'nav pull-right');

            if ($sonataAdminParam = $request->get('_sonata_admin')) {
                // we are in the admin area
                $sonataAdmin = $this->serviceContainer->get($sonataAdminParam);
                if ($id = $request->get('id')) {
                    $entity = $sonataAdmin->getObject($id);
                }
            } else {
                // we are in the frontend
                $entity = $request->get('_content');
            }

            if ($entity instanceof VersionableInterface) {
                if ($snapShot = $entity->getSnapshot()) {
                    $liveRoute = $this->router->generate($snapShot->getRoute());
                }
                $draftRoute = $this->router->generate($entity->getRoute());

                $editUrl = $this->router->generate(
                    'admin_networking_initcms_page_edit',
                    array('id' => urlencode($entity->getId()))
                );
            } elseif ($entity instanceof ResourceVersionInterface) {
                $liveRoute = $this->router->generate($entity->getRoute());
                $draftRoute = $this->router->generate($entity->getPage()->getRoute());

                $editUrl = $this->router->generate(
                    'admin_networking_initcms_page_edit',
                    array('id' => urlencode($entity->getPage()->getId()))
                );

            }

            if (!$sonataAdminParam && $editUrl) {
                $menu->addChild('edit', array('uri' => $editUrl));
            }

            $draftPath = $this->router->generate('networking_init_view_draft', array('path' => urlencode($draftRoute)));
            $livePath = $this->router->generate('networking_init_view_live', array('path' => urlencode($liveRoute)));

            // Set active url based on which status is in the session
            if ($this->serviceContainer->get('session')->get(
                '_viewStatus'
            ) === VersionableInterface::STATUS_PUBLISHED
            ) {
                $menu->setCurrentUri($livePath);
            } else {
                $menu->setCurrentUri($draftPath);
            }

            $menu->addChild('Draft', array('uri' => $draftPath));
            $menu->addChild('Live', array('uri' => $livePath));

        }

        return $menu;
    }
}
