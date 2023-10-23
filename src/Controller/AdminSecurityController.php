<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Controller;

use App\Entity\User;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AdminSecurityController extends AbstractController
{
    /**
     * AdminSecurityController constructor.
     */
    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly Pool $pool,
        private readonly TemplateRegistryInterface $templateRegistry
    ) {
    }

    public function apiLogin(
        #[CurrentUser]
        ?User $user,
        Request $request,
    ): Response {
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }


        $token = $this->getWebToken($user);

        return $this->json([
            'user' => $user->getUserIdentifier(),
            'token' => $token,
            'redirect' => $request->getSession()->get(
                '_security.admin.target_path'
            ),
        ]);
    }

    private function getWebToken(User $user): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

        // Create token payload as a JSON string
        $payload = json_encode(['user_id' => $user->getId()]);

        $base64UrlHeader = str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($header)
        );

        // Encode Payload to Base64Url String
        $base64UrlPayload = str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($payload)
        );

        $signature = hash_hmac(
            'sha256',
            $base64UrlHeader.".".$base64UrlPayload,
            'abC123!',
            true
        );
        $base64UrlSignature = str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($signature)
        );

        return $base64UrlHeader.".".$base64UrlPayload.".".$base64UrlSignature;
    }

    /**
     * @return Response|RedirectResponse
     */
    public function loginAction(Request $request): Response|RedirectResponse {
        if ($this->getUser() instanceof UserInterface) {
            $this->addFlash(
                'sonata_user_error',
                'sonata_user_already_authenticated'
            );
            $url = $this->generateUrl('sonata_admin_dashboard');

            return $this->redirect($url);
        }

        $session = $request->getSession();

        $authErrorKey = \Symfony\Bundle\SecurityBundle\Security::AUTHENTICATION_ERROR;

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif ($session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error
                = null; // The value does not come from the security component.
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            $refererUri = $request->server->get('HTTP_REFERER');

            return $this->redirect(
                $refererUri && $refererUri != $request->getUri()
                    ? $refererUri
                    : $this->generateUrl(
                        'sonata_admin_dashboard'
                    )
            );
        }

        $csrfToken = $this->csrfTokenManager->getToken('authenticate')
            ->getValue();

        return $this->render(
            '@NetworkingInitCms/Admin/Security/login.html.twig',
            [
                'admin_pool' => $this->pool,
                'base_template' => $this->templateRegistry->getTemplate(
                    'layout'
                ),
                'csrf_token' => $csrfToken,
                'error' => $error,
                'last_username' =>$session->get(
                        \Symfony\Bundle\SecurityBundle\Security::LAST_USERNAME
                    ),
                'reset_route' => $this->generateUrl(
                    'sonata_user_admin_resetting_request'
                ),
                'template_registry' => $this->templateRegistry,
            ]
        );
    }


    public function checkAction(): never
    {
        throw new \RuntimeException(
            'You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.'
        );
    }

    public function logoutAction(): never
    {
        throw new \RuntimeException(
            'You must activate the logout in your security firewall configuration.'
        );
    }
}
