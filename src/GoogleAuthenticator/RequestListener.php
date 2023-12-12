<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\GoogleAuthenticator;

use Networking\InitCmsBundle\Helper\OneTimeCodeHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Twig\Environment;

class RequestListener
{
    public function __construct(
        private readonly Helper $helper,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly OneTimeCodeHelper $oneTimeCodeHelper,
        private readonly Environment $twig
    ) {
    }

    public function onCoreRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        $request = $event->getRequest();
        if (!$token) {
            return;
        }

        if (!$token instanceof UsernamePasswordToken) {
            return;
        }

        if (!$this->helper->needToHaveGoogle2FACode($request)) {
            return;
        }

        if (preg_match('/.*\/_profiler\/.*/', $request->getRequestUri())) {
            return;
        }

        if (preg_match('/.*\/_wdt\/.*/', $request->getRequestUri())) {
            return;
        }

        if (preg_match('/.*\/js\/.*/', $request->getRequestUri())) {
            return;
        }

        if (preg_match(
            '/\/admin\/one_time_code.*/',
            $request->getRequestUri()
        )
        ) {
            return;
        }

        $key = $this->helper->getSessionKey($token);
        $request = $event->getRequest();
        $session = $event->getRequest()->getSession();
        $user = $token->getUser();

        if (!$user->hasStepVerificationCode()
            && '/admin/two_factor_setup' !== $request->getRequestUri()
        ) {
            $response = $request->isXmlHttpRequest() ? new JsonResponse(
                ['redirect' => '/admin/two_factor_setup']
            ) : new RedirectResponse('/admin/two_factor_setup');

            $event->setResponse($response);

            return;
        }
        if (!$session->has($key)) {
            return;
        }

        if (true === $session->get($key)) {
            return;
        }

        $state = 'init';
        if ('POST' === $request->getMethod() && $request->get('_code', false)) {
            if (null !== $request->getSession()->get('networing_init_cms.one_time_code')) {
                if (true === $this->oneTimeCodeHelper->checkCode(
                    $request->get('_code'),
                    $user
                )) {
                    $request->getSession()->remove('networing_init_cms.one_time_code');
                    $this->oneTimeCodeHelper->removeOneTimeCodeRequest($request->get('_code'), $user);

                    $session->set($key, true);
                    if ($request->isXmlHttpRequest()) {
                        $event->setResponse(
                            new JsonResponse(['success' => 'success'], 200)
                        );

                        return;
                    }

                    return;
                }
            }

            if (true === $this->helper->checkCode(
                $user,
                $request->get('_code')
            )
            ) {
                $session->set($key, true);
                if ($request->isXmlHttpRequest()) {
                    $event->setResponse(
                        new JsonResponse(['success' => 'success'], 200)
                    );

                    return;
                }

                return;
            }

            $state = 'error';

            if ($request->isXmlHttpRequest()) {
                $event->setResponse(
                    new JsonResponse(['error' => 'Invalid code'], 400)
                );

                return;
            }
        }

        if ($request->isXmlHttpRequest()) {
            return;
        }

        $event->setResponse(
            new Response(
                $this->twig->render(
                    '@NetworkingInitCms/Admin/Security/login.html.twig',
                    [
                        'base_template' => '@NetworkingInitCms/admin_layout.html.twig',
                        'error' => [],
                        'state' => $state,
                        'two_step_submit' => true,
                    ]
                )
            )
        );
    }
}
