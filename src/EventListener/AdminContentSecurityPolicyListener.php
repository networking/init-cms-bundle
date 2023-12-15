<?php

namespace Networking\InitCmsBundle\EventListener;

use Networking\InitCmsBundle\Helper\ContentSecurityPolicyHelper;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class AdminContentSecurityPolicyListener
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();

        // if path is not admin, return
        if (str_contains($request->getPathInfo(), '/admin/login')) {
            return;
        }

        // if path is not admin, return
        if (false === str_contains($request->getPathInfo(), '/admin')) {
            return;
        }

        $response = $event->getResponse();

        $cspHeaders = ContentSecurityPolicyHelper::getCspHeaders($response);

        $types = [
            'script-src',
            'style-src',
            'worker-src',
            'connect-src',
        ];

        foreach ($cspHeaders as $header => $directives) {
            foreach ($types as $type) {
                if (!array_key_exists($type, $directives)) {
                    $cspHeaders[$header][$type] = [];
                    $directives[$type] = [];
                }

                if ('script-src' === $type) {
                    foreach ($directives[$type] as $key => $value) {
                        if (str_contains($value, 'nonce-')) {
                            unset($cspHeaders[$header][$type][$key]);
                        }
                    }
                    if (!in_array("'unsafe-inline'", $directives[$type])) {
                        $cspHeaders[$header][$type][] = "'unsafe-inline'";
                    }
                    if (!in_array("'unsafe-eval'", $directives[$type])) {
                        $cspHeaders[$header][$type][] = "'unsafe-eval'";
                    }
                }

                if ('style-src' === $type) {
                    foreach ($directives[$type] as $key => $value) {
                        if (str_contains($value, 'nonce-')) {
                            unset($cspHeaders[$header][$type][$key]);
                        }
                    }
                    if (!in_array("'unsafe-inline'", $directives[$type])) {
                        $cspHeaders[$header][$type][] = "'unsafe-inline'";
                    }
                }

                if ('worker-src' === $type) {
                    if (!in_array('blob:', $directives[$type])) {
                        $cspHeaders[$header][$type][] = "'unsafe-eval'";
                    }
                }

                if ('connect-src' === $type) {
                    if (!in_array("'self'", $directives[$type])) {
                        $cspHeaders[$header][$type][] = "'self'";
                    }
                    if (!in_array('cke4.ckeditor.com', $directives[$type])) {
                        $cspHeaders[$header][$type][] = 'cke4.ckeditor.com';
                    }
                }
            }
        }

        foreach ($cspHeaders as $header => $directives) {
            $response->headers->set(
                $header,
                ContentSecurityPolicyHelper::generateCspHeader($directives)
            );
        }
    }
}
