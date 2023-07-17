<?php

declare(strict_types=1);

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\EventListener;

use Sonata\AdminBundle\Admin\AdminExtensionInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
     * @var Request
     */
    protected $request;

    public function __construct(Pool $adminPool)
    {
        $this->adminPool = $adminPool;
    }

    /**
     * @return void
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $this->request = $event->getRequest();

        if (!$this->request) {
            return;
        }

        if (!$this->request->hasSession()) {
            return;
        }

        if($this->request->isXmlHttpRequest()){
            return;
        }

        try{
            $admin = $this->adminPool->getAdminByAdminCode($this->request->get('_sonata_admin', ''));
        }catch (\Exception){
            return;
        }

        $admin->setRequest($this->request);

        foreach ($admin->getExtensions() as $extension) {
            $this->setTrackedAction($extension);
        }
        $this->setTrackedAction($admin);
    }

    /**
     * @param $object
     */
    public function setTrackedAction(AdminInterface | AdminExtensionInterface $object)
    {
        if (method_exists($object, 'getTrackedActions')) {

            foreach ($object->getTrackedActions() as $trackedAction) {
                // if an action which is flagged as 'to be tracked' is matching the end of the route: add info to session
                if (preg_match('#'.$trackedAction.'$#', (string) $this->request->get('_route'), $matches)) {
                    $this->updateTrackedInfo(
                        $this->request->getSession(),
                        '_networking_initcms_admin_tracker',
                        [
                            'url' => $this->request->getRequestUri(),
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
    protected function updateTrackedInfo(
        SessionInterface $session,
        string $sessionKey,
        array $trackInfoArray,
        int $limit = 5): void
    {
        try{
            $value = json_decode(
                (string) $session->get($sessionKey, '[]'),
                true,
                512,
                JSON_THROW_ON_ERROR);
        }catch (\Exception){
            $value = [];
        }

        if (is_null($value)) {
            $value = [];
        }


        if(current($value) === $trackInfoArray){
            return;
        }

        array_unshift(
            $value,
            $trackInfoArray
        );

        if ($limit > 0 && (is_countable($value) ? count($value) : 0) > $limit) {
            array_pop($value);
        }

        $session->set($sessionKey, json_encode($value, JSON_THROW_ON_ERROR));
    }
}
