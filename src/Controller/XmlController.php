<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageSnapshot;
use Networking\InitCmsBundle\Model\PageSnapshotInterface;
use Networking\InitCmsBundle\Helper\LanguageSwitcherHelper;

/**
 * Class FrontendPageController
 * @package Networking\InitCmsBundle\Controller
 * @author net working AG <info@networking.ch>
 */
class XmlController extends Controller
{


    /**
     * Render the xml Sitemap
     *
     * @param Request $request
     * @return Response
     */
    public function sitemapAction(Request $request, $locale)
    {
        $params = [];
        $params['domain'] = $this->getDomainName($request);
        $params['languages'] = $this->container->getParameter('networking_init_cms.page.languages');

        if($locale != '' or count($params['languages']) == 1)
        {
            //sitemap ausgeben
            if($locale == '')
            {  //use locale as default value
                $locale = $params['languages']['locale'];
            }
            $page_filter = ['visibility' => 'public', 'status' => 'status_published', 'locale' => $locale];
            $em = $this->getDoctrine()->getManager();
            $params['pages'] = $em->getRepository('ApplicationNetworkingInitCmsBundle:Page')->findBy($page_filter);
            $params['additional_links'] = $this->getAdditionalLinks($locale);
            //render xml
            $response =  $this->render(
                'NetworkingInitCmsBundle:Sitemap:sitemap.xml.twig',
                $params
            );
        }
        else
        {
            //multilanguage site, return language overview sitemap

            /* TODO , check if / how "last modified" is possible */
            $response =  $this->render(
                'NetworkingInitCmsBundle:Sitemap:multilingual_sitemap.xml.twig',
                $params
            );
        }

        $response->headers->set('Content-Type', 'application/xml');
        return $response;
    }




    /*
     * get list of additional links from the config
     * */
    private function getAdditionalLinks($locale)
    {
        $return = [];
        $additional_links =  $this->container->getParameter('networking_init_cms.xml_sitemap.additional_links');
        foreach($additional_links as $links){
            if($links['locale'] == $locale){
                return $links['links'] ;
            }
        }
        return $return;
    }

    /**
     * check config for domain name, otherwise returns scheme & host
     */
    private function getDomainName(Request $request)
    {
        $domain =  $this->container->getParameter('networking_init_cms.xml_sitemap.sitemap_url');
        if($domain == ''){
            //domain is not set in config.yml
            $domain = $request->getScheme()."://".$request->getHost();
        }

        return $domain;
    }

}
