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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Routing\Router;


/**
 * @author net working AG <info@networking.ch>
 */
class CmsHelperController extends Controller
{

    /**
     * @Route("/admin/init_cms.js", name="networking_init_cms_js")
     */
    public function initCmsJsAction()
    {

        $response = $this->render('NetworkingInitCmsBundle:Core:init_cms.js.twig');

        $response->headers->add(array('content-type' => 'text/javascript'));

        return $response;
    }

    /**
     * @Route("/admin/ckeditor.js", name="networking_init_cms_ckeditor_config")
     */
    public function ckeditorConfigAction()
    {

        $ckeditorConfig = $this->container->getParameter('networking_init_cms.ckeditor_config');

        $response = $this->render('NetworkingInitCmsBundle:Core:ckeditor_config.js.twig', array('ckeditor_style' => $ckeditorConfig));

        $response->headers->add(array('content-type' => 'text/javascript'));

        return $response;
    }

}
