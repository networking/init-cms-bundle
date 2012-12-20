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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\DependencyInjection\Container;
use Mopa\Bundle\BootstrapBundle\Navbar\AbstractNavbarMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\Iterator\RecursiveItemIterator;

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
    public function __construct(FactoryInterface $factory, SecurityContextInterface $securityContext, Container $serviceContainer)
    {
        parent::__construct($factory);

        $this->securityContext = $securityContext;
        $this->isLoggedIn = $this->securityContext->isGranted('IS_AUTHENTICATED_FULLY');
        $this->serviceContainer = $serviceContainer;
        $this->router = $this->serviceContainer->get('router');
    }

    public function createViewStatusMenu(Request $request)
    {
        $path = '';

        $menu = $this->factory->createItem('root');


        if ($this->isLoggedIn) {
            $menu->setChildrenAttribute('class', 'nav pull-right');

            if ($sonataAdminParam = $request->get('_sonata_admin')) {
                /** @var $sonataAdmin \Sonata\AdminBundle\Admin\Admin */
                $sonataAdmin = $this->serviceContainer->get($sonataAdminParam);
                if ($id = $request->get('id')) {
                    /** @var $entity \Networking\InitCmsBundle\Entity\Page */
                    $entity = $sonataAdmin->getObject($id);
                    $path = $this->router->generate($entity->getRoute());
                }
            }

//            $admin = $adminHelper->pool->getInstance('networking_init_cms.page.admin.page');


            $menu->addChild('Draft', array('uri' => $this->router->generate('networking_init_view_draft', array('path' => urlencode($path)))));
            $menu->addChild('Live', array('uri' => $this->router->generate('networking_init_view_live', array('path' => urlencode($path)))));

            $this->addDivider($menu, true);
        }


        return $menu;
    }
}
