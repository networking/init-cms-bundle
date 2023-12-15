<?php

namespace Networking\InitCmsBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class MaintenanceListener
{

    public function __construct(private readonly string $projectDir)
    {

    }
    public function onKernelRequest(RequestEvent $event): void
    {
        //check if maintenance flag file exsits



        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }

        $pathInfo = $event->getRequest()->getPathInfo();


        if(str_contains($pathInfo, '/maintenance.html') === true){
            return;
        }

        if(str_contains($pathInfo, '/_profiler') === true){
            return;
        }

        if(str_contains($pathInfo, '/_wdt') === true){
            return;
        }

        $request = $event->getRequest();

        if (file_exists($this->projectDir . '/maintenance.flag')) {
            //check if IP address is conatined in the whitelist in the maintenance.flag file
            $maintenanceFile = file_get_contents($this->projectDir . '/maintenance.flag');
            $ipWhitelist = explode("\n", $maintenanceFile);

            $ip = $request->getClientIp();

            if (in_array($ip, $ipWhitelist) === true) {
                return;
            }

            if (str_contains($pathInfo, '/admin') === false) {
                //if it is not for the admin, redirect to the maintenance page
                $event->setResponse(new RedirectResponse('/maintenance.html'));
            }
        }

    }
}