<?php

namespace Networking\InitCmsBundle\Action;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

readonly class MaintenanceAction
{
    public function __construct(
        private Environment $twig,
        private string $projectDir)
    {
    }

    /**
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\LoaderError
     */
    public function __invoke(Request $request): Response
    {
        $maintenanceFile = $this->projectDir.'/maintenance.flag';

        if (!file_exists($maintenanceFile) && 'networking_init_cms_maintenance_debug' !== $request->attributes->get('_route')) {
            return new RedirectResponse('/');
        }

        $html = $this->twig->render('@NetworkingInitCms/Maintenance/maintenance.html.twig');

        $status = 503;

        if ('networking_init_cms_maintenance_debug' === $request->attributes->get('_route')) {
            $status = 200;
        }

        return new Response($html, $status);
    }
}
