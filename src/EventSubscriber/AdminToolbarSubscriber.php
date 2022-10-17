<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\EventSubscriber;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Twig\Environment;

/**
 * AdminToolbarSubscriber injects the Admin Toolbar.
 *
 * The onKernelResponse method must be connected to the kernel.response event.
 *
 * The Admin Toolbar is only injected on well-formed HTML (with a proper </body> tag).
 * This means that the Admin Toolbar is never included in sub-requests or ESI requests.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class AdminToolbarSubscriber implements EventSubscriberInterface
{
    const DISABLED = 1;
    const ENABLED = 2;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var int
     */
    protected $mode;

    /**
     * @var string
     */
    protected $position;

    public function __construct(Environment $twig, AuthorizationCheckerInterface $securityContext,  $mode = self::ENABLED, $position =
    'top')
    {
        $this->twig = $twig;
        $this->authorizationChecker = $securityContext;
        $this->mode = (int) $mode;
        $this->position = $position;
    }

    public function isEnabled()
    {
        return self::DISABLED !== $this->mode;
    }

    /**
     * @param ResponseEvent $event
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();

        // do not capture redirects or modify XML HTTP Requests
        if ($request->isXmlHttpRequest()) {
            return;
        }

        // do not capture admin cms urls
        if (preg_match('/.*\/admin\/.*/', $request->getRequestUri())) {
            return;
        }

        // do not capture profiler urls
        if (preg_match('/.*\/_profiler\/.*/', $request->getRequestUri())) {
            return;
        }

        try {
            if (!$this->authorizationChecker->isGranted('ROLE_SONATA_ADMIN')) {
                return;
            }
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return;
        }

        if (self::DISABLED === $this->mode
            || $response->isRedirection()
            || ($response->headers->has('Content-Type') && false === strpos($response->headers->get('Content-Type'), 'html'))
            || 'html' !== $request->getRequestFormat()
        ) {
            return;
        }

        $this->injectToolbar($response, $request);
    }

    /**
     * Injects the admin toolbar into the given Response.
     *
     * @param Response $response
     * @param Request  $request
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function injectToolbar(Response $response, Request $request)
    {
        if (function_exists('mb_stripos')) {
            $posrFunction = 'mb_strripos';
            $substrFunction = 'mb_substr';
        } else {
            $posrFunction = 'strripos';
            $substrFunction = 'substr';
        }

        $content = $response->getContent();
        $pos = $posrFunction($content, '</body>');

        $page = $request->get('_content', false);

        $page_id = null;
        if ($page instanceof \Networking\InitCmsBundle\Model\PageSnapshot) {
            $page_id = $page->getPage()->getId();
        } elseif ($page instanceof \Networking\InitCmsBundle\Model\Page) {
            $page_id = $page->getId();
        }


        if (false !== $pos) {
            $toolbar = "\n".str_replace("\n", '', $this->twig->render(
                '@NetworkingInitCms/Admin/toolbar_js.html.twig',
                [
                    'position' => $this->position,
                    'page_id' => $page_id,
                ]
            ))."\n";
            $content = $substrFunction($content, 0, $pos).$toolbar.$substrFunction($content, $pos);
            $response->setContent($content);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', -128],
        ];
    }
}
