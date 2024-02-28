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

namespace Networking\InitCmsBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FrontendPageController.
 *
 * @author net working AG <info@networking.ch>
 */
class XmlController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected string $pageClass,
        protected array $languages = [],
        protected array $additionalLinks = [],
        protected string $domainName = ''
    ) {
    }

    public function siteMapAction(Request $request, $locale): Response
    {
        $params = [
            'domain' => $this->getDomainName($request),
            'languages' => $this->languages,
        ];

        $template = '@NetworkingInitCms/Sitemap/multilingual_sitemap.xml.twig';

        if ($locale || 1 === count((array) $this->languages)) {
            $locale = $locale ?: $params['languages'][0]['locale'];
            $page_filter = ['visibility' => 'public', 'status' => 'status_published', 'locale' => $locale];

            $params['pages'] = $this->em->getRepository($this->pageClass)->findBy($page_filter);
            $params['additional_links'] = $this->getAdditionalLinks($locale);

            $template = '@NetworkingInitCms/Sitemap/sitemap.xml.twig';
        }

        $response = $this->render($template, $params);
        $response->headers->set('Content-Type', 'application/xml');

        return $response;
    }

    private function getAdditionalLinks($locale): array
    {
        foreach ($this->additionalLinks as $links) {
            if ($links['locale'] == $locale) {
                return $links['links'];
            }
        }

        return [];
    }

    private function getDomainName(Request $request): string
    {
        return $this->domainName ?: $request->getScheme().'://'.$request->getHost();
    }
}
