<?php

namespace Networking\InitCmsBundle\Action;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Request;

class MaintenanceAction
{
    public function __construct(
        private Environment $twig,
        private string $projectDir)
    {

    }
    public function __invoke(Request $request): Response
    {
        $maintenanceFile = $this->projectDir . '/maintenance.flag';

        if(!file_exists($maintenanceFile) && 'networking_init_cms_maintenance_debug' !== $request->get('_route')) {
            return new RedirectResponse('/');
        }

        $html = $this->twig->render('@NetworkingInitCms/Maintenance/maintenance.html.twig');


        $status = 503;

        if('networking_init_cms_maintenance_debug' === $request->get('_route')) {
            $status = 200;
        }

        return new Response($html, $status);
    }
}