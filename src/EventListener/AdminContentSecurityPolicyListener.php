<?php

namespace Networking\InitCmsBundle\EventListener;

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

        $cspHeaders = $response->headers->all('content-security-policy');

        if (count($cspHeaders) > 0) {
            $cspHeader = $cspHeaders[0];

            // Add 'unsafe-inline' to the script-src directive
            $cspHeader = str_replace(
                'script-src',
                'script-src \'unsafe-inline\' \'unsafe-eval\'',
                $cspHeader
            );
            // Add 'unsafe-inline' to the style-src directive
            $cspHeader = str_replace(
                'style-src',
                'style-src \'unsafe-inline\'',
                $cspHeader
            );

            if (false === str_contains($cspHeader, 'blob:')
                && true === str_contains($cspHeader, 'worker-src')
            ) {
                $cspHeader = str_replace(
                    'worker-src',
                    'worker-src blob:',
                    $cspHeader
                );
            }

            if (false === str_contains($cspHeader, 'worker-src')) {
                $cspHeader .= '; worker-src blob:';
            }

            if (false === str_contains($cspHeader, 'cke4.ckeditor.com')
                && true === str_contains($cspHeader, 'connect-src')
            ) {
                $cspHeader = str_replace(
                    'connect-src',
                    'connect-src cke4.ckeditor.com',
                    $cspHeader
                );
            }

            if (false === str_contains($cspHeader, 'connect-src')) {
                $cspHeader .= '; connect-src \'self\' cke4.ckeditor.com';
            }

            @header_remove('x-powered-by');

            // remove 'nonce-*' from script-src directive
            $cspHeader = preg_replace(
                '/\'nonce-[a-zA-Z0-9_-]+\'/',
                '',
                $cspHeader
            );
            $response->headers->set('content-security-policy', [$cspHeader]);
            $response->headers->set('x-content-security-policy', [$cspHeader]);
        }
    }
}
