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
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class FrontendPageController.
 *
 * @author net working AG <info@networking.ch>
 */
class CacheController extends Controller
{
    /**
     * @var AuthorizationChecker
     */
    protected $authorizationChecker;

    /**
     * CacheController constructor.
     *
     * @param AuthorizationChecker $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * clear the Cache.
     *
     * @return Response
     */
    public function clearAction()
    {

        /*
         * to do: check if logged in user is sysadmin
         * */
        if ($this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            /** @var \Networking\InitCmsBundle\Lib\PhpCacheInterface $phpCache */
            $phpCache = $this->get('networking_init_cms.lib.php_cache');
            $phpCache->clean();

            /*clean function does not return status, therefore set success to true */
            $success = true;
            $response = ['success' => $success];

            return new Response(json_encode($response));
        } else {
            /*wrong autorisation */
            $success = false;
            $response = ['success' => $success];

            return new Response(json_encode($response));
        }
    }
}
