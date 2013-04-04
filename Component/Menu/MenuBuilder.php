<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Component\Menu;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Security\Core\SecurityContextInterface,
    Symfony\Component\DependencyInjection\Container,
    Mopa\Bundle\BootstrapBundle\Navbar\AbstractNavbarMenuBuilder,
    Knp\Menu\FactoryInterface,
    Knp\Menu\Iterator\RecursiveItemIterator,
    Networking\InitCmsBundle\Entity\MenuItem as Menu,
    Networking\InitCmsBundle\Entity\MenuItemRepository,
    Networking\InitCmsBundle\Component\Menu\MenuSubItemFilterIterator,
    Networking\InitCmsBundle\Entity\Page;

/**
 * @author net working AG <info@networking.ch>
 */
class MenuBuilder extends AbstractNavbarMenuBuilder
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
     * @var object
     */
    protected $router;

    /**
     * @var string
     */
    protected $viewStatus;

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
            $this->isLoggedIn = true;
        }
        $this->serviceContainer = $serviceContainer;
        $this->router = $this->serviceContainer->get('router');

        $this->viewStatus = $serviceContainer->get('session')->get('_viewStatus')
            ? $serviceContainer->get('session')->get('_viewStatus')
            : Page::STATUS_PUBLISHED;
    }


    /**
     * Creates the login and change language navigation for the right side of the top frontend navigation
     *
     * @param string $classes
     * @internal param \Symfony\Component\HttpFoundation\Request $request
     * @internal param $languages
     * @return \Knp\Menu\ItemInterface
     */
    public function createLoginMenu($classes = 'nav pull-right')
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', $classes );

        if ($this->isLoggedIn) {
            $menu->addChild($this->get('translator')->trans('logout'), array('route' => 'fos_user_security_logout'));
        } else {
            $menu->addChild($this->get('translator')->trans('login'), array('route' => 'fos_user_security_login'));
            $menu->addChild(
                $this->get('translator')->trans('register'),
                array('route' => 'fos_user_registration_register')
            );
        }


        return $menu;
    }


    /**
     * Recursively get parents
     *
     * @param $menu
     * @param $childNode
     * @param $depth
     * @param  int   $level
     * @return mixed
     */
    public function getMenuParentItem($menu, $childNode, $depth, $level = 0)
    {
        if ($depth === $level) {
            return $menu->offsetGet($childNode->getName());
        }

        return $this->getMenuParentItem($menu, $childNode->getParent(), $depth, ++$level);
    }

    /**
     * Create an new node using the ContentRoute object to generate the uri
     *
     * @param  \Networking\InitCmsBundle\Entity\MenuItem $node
     * @return \Knp\Menu\ItemInterface
     */
    public function createFromNode(Menu $node)
    {
        if ($node->getRedirectUrl()) {
            $uri = $node->getRedirectUrl();
        }
        elseif($node->getInternalUrl()){
            $uri = $node->getInternalUrl();
            try{
                $routeArray = $route = $this->router->matchRequest(Request::create($uri));
                if(is_array($routeArray) && array_key_exists('_route', $routeArray)){
                    $uri = $this->router->generate($routeArray['_route']);
                }
            }catch(\Exception $e){
                //do nothing
            }

        }else {
            if ($this->viewStatus == Page::STATUS_PUBLISHED) {
                if ($snapshot = $node->getPage()->getSnapshot()) {
                    $route = $snapshot->getRoute();
                    $uri = $this->router->generate($route);
                } else {
                    return;
                }
            } else {
                $route = $node->getPage()->getRoute();
                $uri = $this->router->generate($route);
            }
        }
        $options = array(
            'uri' => $uri,
            'label' => $node->getName(),
            'attributes' => array(),
            'linkAttributes' => $node->getLinkAttributes(),
            'childrenAttributes' => array(),
            'labelAttributes' => array(),
            'extras' => array(),
            'display' => true,
            'displayChildren' => true,

        );
        $item = $this->factory->createItem($node->getName(), $options);

        return $item;
    }

    /**
     * @param $id
     * @return object
     */
    public function get($id)
    {
        return $this->serviceContainer->get($id);
    }
}
