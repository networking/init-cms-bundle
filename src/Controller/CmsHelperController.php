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

use Networking\InitCmsBundle\Entity\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CmsHelperController.
 *
 * @author net working AG <info@networking.ch>
 */
class CmsHelperController extends AbstractController
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }


    /**
     * Set user Admin preferred width.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function setAdminPortalWidthAction(Request $request)
    {
        $size = $request->get('size', 'full');

        $user = $this->getUser();
        $status = 200;
        $message = 'OK';
        try {
            $user->setLastActivity(new \DateTime());
            $user->setAdminSetting('admin_portal_width', $size);
            $this->userManager->updateUser($user);
        } catch (\Exception $e) {
            $status = 500;
            $message = $e->getMessage();
        }

        return new JsonResponse(['message' => $message, 'size' => $size, 'admin_portal_width' => $user->getAdminSetting('admin_portal_width')], $status);
    }
}
