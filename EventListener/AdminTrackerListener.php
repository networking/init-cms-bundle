<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Symfony\Component\Security\Http\AccessMapInterface,
    Sonata\AdminBundle\Admin\Pool,
    Symfony\Component\HttpKernel\Event\GetResponseEvent,
    Sonata\AdminBundle\Admin\AdminInterface;

/**
 * @author sonja brodersen <s.brodersen@networking.ch>
 */
class AdminTrackerListener
{
    /**
     * @var \Sonata\AdminBundle\Admin\Pool $adminPool
     */
    protected $adminPool;

    /**
     * @var \Sonata\AdminBundle\Admin\AdminInterface $admin
     */
    protected $admin;


    /**
     * @param \Sonata\AdminBundle\Admin\Pool $adminPool
     */
    public function __construct(Pool $adminPool)
    {
        $this->adminPool = $adminPool;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @throws \RuntimeException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request) {
            return;
        }

        if (!$request->getSession()) {
            return;
        }

        $adminCode = $request->get('_sonata_admin');

        if(!is_null($adminCode)){
            $this->admin = $this->adminPool->getAdminByAdminCode($adminCode);
            if (!$this->admin) {
                throw new \RuntimeException(sprintf('Unable to find the admin class related to the current controller (%s)', get_class($this)));
            }
            $this->admin->setRequest($request);
var_dump($this->admin->getBaseControllerName());die;
            $url = $request->getPathInfo();

//            var_dump($url);die;
//            $action = $this->admin->getCurrentAction();
            $action = 'edit';
            $trackedActions = $this->admin->getTrackedActions();
            if(in_array($action, $trackedActions)){
                // track this action in session
                var_dump($action);
            }
        }
    }
}