<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\GoogleAuthenticator;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Twig\Environment;

class RequestListener
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * NEXT_MAJOR: Remove `$templating` argument and make `$twig` argument mandatory.
     *
     * @param EngineInterface|Environment $templating
     */
    public function __construct(Helper $helper, TokenStorageInterface $tokenStorage, Environment $twig = null)
    {
        $this->helper = $helper;
        $this->tokenStorage = $tokenStorage;
        $this->twig = $twig;
    }

    public function onCoreRequest(RequestEvent $event): void
    {
        if (HttpKernel::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return;
        }

        if (!$token instanceof UsernamePasswordToken) {
            return;
        }

        $key = $this->helper->getSessionKey($token);
        $request = $event->getRequest();
        $session = $event->getRequest()->getSession();
        $user = $token->getUser();

        if (!$session->has($key)) {
            return;
        }

        if (true === $session->get($key)) {
            return;
        }

        $state = 'init';
        if ('POST' === $request->getMethod()) {
            if (true === $this->helper->checkCode($user, $request->get('_code'))) {
                $session->set($key, true);

                return;
            }

            $state = 'error';
        }

        // NEXT_MAJOR: Remove the following check and the `else` condition
        if ($this->twig) {
            $event->setResponse(new Response($this->twig->render('@NetworkingInitCms/Admin/Security/login.html.twig', [
                'base_template' => '@NetworkingInitCms/admin_layout.html.twig',
                'error' => [],
                'state' => $state,
                'two_step_submit' => true,
            ])));
        } else {
            $event->setResponse($this->templating->renderResponse('@NetworkingInitCms/Admin/Security/login.html.twig', [
                'base_template' => '@NetworkingInitCms/admin_layout.html.twig',
                'error' => [],
                'state' => $state,
                'two_step_submit' => true,
            ]));
        }
    }
}
