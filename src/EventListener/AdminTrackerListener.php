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

use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Class AdminTrackerListener.
 *
 * @author sonja brodersen <s.brodersen@networking.ch>
 */
class AdminTrackerListener
{
    /**
     * @var \Sonata\AdminBundle\Admin\Pool
     */
    protected $adminPool;

    /**
     * @var \Sonata\AdminBundle\Admin\AdminInterface
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
     * @param RequestEvent $event
     *
     * @return void
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (!$request) {
            return;
        }

        if (!$request->hasSession()) {
            return;
        }

        if($request->isXmlHttpRequest()){
            return;
        }

        $adminCode = $request->get('_sonata_admin');

        if (!is_null($adminCode)) {
            $this->admin = $this->adminPool->getAdminByAdminCode($adminCode);
            $this->admin->setRequest($request);
            if (!$this->admin) {
                throw new \RuntimeException(sprintf(
                    'Unable to find the admin class related to the current controller (%s)',
                    get_class($this)
                ));
            }

            foreach ($this->admin->getExtensions() as $extension) {
                $this->setTrackedAction($extension);
            }
            $this->setTrackedAction($this->admin);
        }
    }

    /**
     * @param $object
     */
    public function setTrackedAction($object)
    {
        if (method_exists($object, 'getTrackedActions')) {
            $request = $this->admin->getRequest();

            foreach ($object->getTrackedActions() as $trackedAction) {
                // if an action which is flagged as 'to be tracked' is matching the end of the route: add info to session
                if (preg_match('#'.$trackedAction.'$#', $request->get('_route'), $matches)) {
                    $this->updateTrackedInfo(
                        $request->getSession(),
                        '_networking_initcms_admin_tracker',
                        [
                            'url' => $request->getRequestUri(),
                            'controller' => $this->admin->getBaseControllerName(),
                            'action' => $trackedAction,
                        ]
                    );
                }
            }
        }
    }

    /**
     * update tracker info in session.
     *
     * @param $session
     * @param string $sessionKey
     * @param array  $trackInfoArray
     * @param int    $limit
     */
    protected function updateTrackedInfo($session, $sessionKey, $trackInfoArray, $limit = 5)
    {
        // save the url, controller and action in the session
        $value = json_decode($session->get($sessionKey), true);
        if (is_null($value)) {
            $value = [];
        }
        // add new value as first value (to the top of the stack)
        array_unshift(
            $value,
            $trackInfoArray
        );

        // remove last value, if array has more than limit items
        if ($limit > 0 and count($value) > $limit) {
            array_pop($value);
        }

        // set the session value
        $session->set($sessionKey, json_encode($value));
    }
}
