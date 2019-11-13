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

use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class FrontendPageController.
 *
 * @author net working AG <info@networking.ch>
 */
class CacheController extends AbstractController
{
    /**
     * @var AuthorizationChecker
     */
    protected $authorizationChecker;

    protected $pageCache;

    /**
     * CacheController constructor.
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param PageCacheInterface $pageCache
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, PageCacheInterface $pageCache)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->pageCache = $pageCache;
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
            $this->pageCache->clean();

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
