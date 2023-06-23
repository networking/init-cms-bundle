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
    /**
     * @var EntityManagerInterface
     */
    public $objectManager;

    /**
     * XmlController constructor.
     * @param $pageClass
     * @param array $languages
     * @param array $additionalLinks
     * @param string $domainName
     * @param string $pageClass
     */
    public function __construct(
        EntityManagerInterface $objectManager,
        public $pageClass,
        public $languages = [],
        public $additionalLinks = [],
        public $domainName = ''
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param $locale
     * @return Response
     */
    public function siteMapAction(Request $request, $locale)
    {
        $params = [
            'domain' => $this->getDomainName($request),
            'languages' => $this->languages
        ];

        $template = 'Sonata\\AdminBundle\\FieldDescriptionI\\FieldDescriptionInterface::/Sitemap/multilingual_sitemap.xml.twig';

        if ($locale || count((array) $this->languages) === 1) {
            $locale = $locale?:$params['languages']['locale'];
            $page_filter = ['visibility' => 'public', 'status' => 'status_published', 'locale' => $locale];

            $params['pages'] = $this->objectManager->getRepository($this->pageClass)->findBy($page_filter);
            $params['additional_links'] = $this->getAdditionalLinks($locale);

            $template = '@NetworkingInitSitemap/sitemap.xml.twig';
        }

        $response = $this->render($template,$params);
        $response->headers->set('Content-Type', 'application/xml');

        return $response;
    }

    /**
     * @param $locale
     * @return array
     */
    private function getAdditionalLinks($locale)
    {
        foreach ($this->additionalLinks as $links) {
            if ($links['locale'] == $locale) {
                return $links['links'];
            }
        }

        return [];
    }

    /**
     * @return string
     */
    private function getDomainName(Request $request)
    {
      return $this->domainName?:$request->getScheme().'://'.$request->getHost();
    }
}
