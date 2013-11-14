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
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CmsHelperController
 * @package Networking\InitCmsBundle\Controller
 *
 * @author net working AG <info@networking.ch>
 */
class CmsHelperController extends Controller
{

    /**
     * @return Response
     */
    public function initCmsJsAction()
    {

        $response = $this->render('NetworkingInitCmsBundle:Core:init_cms.js.twig');

        $response->headers->add(array('content-type' => 'text/javascript'));

        return $response;
    }

}
