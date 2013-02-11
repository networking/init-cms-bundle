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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Networking\InitCmsBundle\Entity\Page;
use Networking\InitCmsBundle\Entity\PageSnapshot;
use Networking\InitCmsBundle\Helper\LanguageSwitcherHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Networking\InitCmsBundle\Entity\HelpTextRepository;


/**
 * @author net working AG <info@networking.ch>
 */
class DefaultController extends Controller
{
    /**
     * @Route("/admin/", name="networking_init_cms_admin")
     */
    public function adminAction()
    {
        $url = $this->generateUrl('sonata_admin_dashboard');

        return $this->redirect($url);
    }
}
