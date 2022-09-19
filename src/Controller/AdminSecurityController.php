<?php
/**
 * Created by PhpStorm.
 * User: yorkie
 * Date: 21.12.17
 * Time: 16:41.
 */

namespace Networking\InitCmsBundle\Controller;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class AdminSecurityController extends AbstractController
{
    /**
     * @var CsrfTokenManagerInterface
     */
    private $csrfTokenManager;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var TemplateRegistryInterface
     */
    private $templateRegistry;

    /**
     * AdminSecurityController constructor.
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @param Pool $pool
     * @param TemplateRegistryInterface $templateRegistry
     */
    public function __construct(
        CsrfTokenManagerInterface $csrfTokenManager,
        Pool $pool,
        TemplateRegistryInterface $templateRegistry
    ) {

        $this->csrfTokenManager = $csrfTokenManager;
        $this->pool = $pool;
        $this->templateRegistry = $templateRegistry;
    }

    /**
     * @param Request $request
     *
     * @return Response|RedirectResponse
     */
    public function loginAction(Request $request)
    {
        if ($this->getUser() instanceof UserInterface) {
            $this->addFlash('sonata_user_error', 'sonata_user_already_authenticated');
            $url = $this->generateUrl('sonata_admin_dashboard');

            return $this->redirect($url);
        }

        $session = $request->getSession();

        $authErrorKey = Security::AUTHENTICATION_ERROR;

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            $refererUri = $request->server->get('HTTP_REFERER');

            return $this->redirect(
                $refererUri && $refererUri != $request->getUri() ? $refererUri : $this->generateUrl(
                    'sonata_admin_dashboard'
                )
            );
        }

        $csrfToken = $this->csrfTokenManager->getToken('authenticate')->getValue();

        return $this->render(
            '@NetworkingInitCms/Admin/Security/login.html.twig',
            [
                'admin_pool' => $this->pool,
                'base_template' => $this->templateRegistry->getTemplate('layout'),
                'csrf_token' => $csrfToken,
                'error' => $error,
                'last_username' => (null === $session) ? '' : $session->get(Security::LAST_USERNAME),
                'reset_route' => $this->generateUrl('sonata_user_admin_resetting_request'),
                'template_registry' => $this->templateRegistry
            ]
        );
    }

    public function checkAction(): void
    {
        throw new \RuntimeException(
            'You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.'
        );
    }

    public function logoutAction(): void
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }
}
