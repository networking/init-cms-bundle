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

use  Sonata\AdminBundle\Admin\Pool;
use  Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class AdminTrackerListener
 * @package Networking\InitCmsBundle\EventListener
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

        if (!is_null($adminCode)) {
            $this->admin = $this->adminPool->getAdminByAdminCode($adminCode);
            if (!$this->admin) {
                throw new \RuntimeException(sprintf(
                    'Unable to find the admin class related to the current controller (%s)',
                    get_class($this)
                ));
            }

            if (method_exists($this->admin, 'getTrackedActions')) {
                foreach ($this->admin->getTrackedActions() as $trackedAction) {
                    // if an action which is flagged as 'to be tracked' is matching the end of the route: add info to session
                    if (preg_match('#' . $trackedAction . '$#', $request->get('_route'), $matches)) {
                        $this->updateTrackedInfo(
                            $request->getSession(),
                            '_networking_initcms_admin_tracker',
                            array(
                                'url' => $request->getRequestUri(),
                                'controller' => $this->admin->getBaseControllerName(),
                                'action' => $trackedAction
                            )
                        );
                    }
                }
            }
        }
    }


    /**
     * update tracker info in session
     * @param $session
     * @param string $sessionKey
     * @param array $trackInfoArray
     * @param int $limit
     */
    protected function updateTrackedInfo($session, $sessionKey, $trackInfoArray, $limit = 5)
    {
        // save the url, controller and action in the session
        $value = json_decode($session->get($sessionKey), true);
        if (is_null($value)) {
            $value = array();
        }
        // add new value as first value (to the top of the stack)
        array_unshift(
            $value,
            $trackInfoArray
        );

        // remove last value, if array has more than limit items
        if ($limit > 0 AND count($value) > $limit) {
            array_pop($value);
        }

        // set the session value
        $session->set($sessionKey, json_encode($value));
    }
}