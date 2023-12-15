<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\EventSubscriber;

use Networking\InitCmsBundle\Helper\ContentSecurityPolicyHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
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
    final public const DISABLED = 1;
    final public const ENABLED = 2;

    final public const PAGE_CACHE_HEADER = 'X-Init-Cms-Cache';

    public function __construct(
        private readonly Environment $twig,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly int $mode = self::ENABLED,
    ) {
    }

    public function isEnabled()
    {
        return self::DISABLED !== $this->mode;
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }

        $response = $event->getResponse();

        if ($response->headers->has(self::PAGE_CACHE_HEADER)) {
            $this->publishedPageNonce($response);
        }

        $response->headers->remove(self::PAGE_CACHE_HEADER);

        $request = $event->getRequest();

        // do not capture redirects or modify XML HTTP Requests
        if ($request->isXmlHttpRequest()) {
            return;
        }

        // do not capture admin cms urls
        if (preg_match('/.*\/admin.*/', $request->getRequestUri())) {
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
        } catch (AuthenticationCredentialsNotFoundException) {
            return;
        }

        if (self::DISABLED === $this->mode
            || $response->isRedirection()
            || ($response->headers->has('Content-Type')
                && !str_contains(
                    $response->headers->get('Content-Type'),
                    'html'
                ))
            || 'html' !== $request->getRequestFormat()
        ) {
            return;
        }

        $this->injectToolbar($response, $request);
    }

    protected function publishedPageNonce(Response $response)
    {
        $content = $response->getContent();
        $scriptNonces = [];
        $styleNonces = [];
        preg_match_all(
            '/<script[^>]*nonce="([^"]*)"[^>]*>/i',
            $content,
            $matches
        );

        if (isset($matches[1])) {
            $scriptNonces = array_unique($matches[1]);
        }

        preg_match_all(
            '/<style[^>]*nonce="([^"]*)"[^>]*>/i',
            $content,
            $matches
        );

        if (isset($matches[1])) {
            $styleNonces = array_unique($matches[1]);
        }

        $headers = ContentSecurityPolicyHelper::getCspHeaders($response);

        foreach ($headers as $header => $directives) {
            if (isset($directives['script-src'])) {
                foreach ($scriptNonces as $nonce) {
                    if (!in_array("'nonce-".$nonce."'", $directives['script-src'])) {
                        $directives['script-src'][] = "'nonce-".$nonce."'";
                    }
                }
            }
            if (isset($directives['style-src'])) {
                foreach ($styleNonces as $nonce) {
                    if (!in_array("'nonce-".$nonce."'", $directives['style-src']) && !in_array("'unsafe-inline'", $directives['style-src'])) {
                        $directives['style-src'][] = "'nonce-".$nonce."'";
                    }
                }
            }
            $response->headers->set(
                $header,
                ContentSecurityPolicyHelper::generateCspHeader($directives)
            );
        }
    }

    /**
     * Injects the admin toolbar into the given Response.
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
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
        if ($page instanceof \Networking\InitCmsBundle\Entity\PageSnapshot) {
            $page_id = $page->getPage()->getId();
        } elseif ($page instanceof \Networking\InitCmsBundle\Entity\BasePage) {
            $page_id = $page->getId();
        }

        // / Add nonce to CSP header
        $cspHeaders = ContentSecurityPolicyHelper::getCspHeaders($response);
        $nonce = bin2hex(random_bytes(16));

        if (count($cspHeaders) > 0) {
            foreach ($cspHeaders as $type => $header) {
                $cspHeaders[$type]['script-src'][] = "'nonce-".$nonce."'";
            }
        }

        foreach ($cspHeaders as $header => $directives) {
            $response->headers->set(
                $header,
                ContentSecurityPolicyHelper::generateCspHeader($directives)
            );
        }

        if (false !== $pos) {
            $toolbar = "\n".str_replace(
                "\n",
                '',
                $this->twig->render(
                    '@NetworkingInitCms/Admin/toolbar_js.html.twig',
                    [
                        'nonce' => $nonce,
                        'page_id' => $page_id,
                    ]
                )
            )."\n";
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
