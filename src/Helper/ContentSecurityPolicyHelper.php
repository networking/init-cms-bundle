<?php

namespace Networking\InitCmsBundle\Helper;

use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicyHelper
{
    /**
     * Retrieves the Content-Security-Policy headers (either X-Content-Security-Policy or Content-Security-Policy) from
     * a response.
     */
    public static function getCspHeaders(Response $response): array
    {
        $headers = [];

        if ($response->headers->has('Content-Security-Policy')) {
            $headers['Content-Security-Policy'] = self::parseDirectives($response->headers->get('Content-Security-Policy'));
        }

        if ($response->headers->has('Content-Security-Policy-Report-Only')) {
            $headers['Content-Security-Policy-Report-Only'] = self::parseDirectives($response->headers->get('Content-Security-Policy-Report-Only'));
        }

        if ($response->headers->has('X-Content-Security-Policy')) {
            $headers['X-Content-Security-Policy'] = self::parseDirectives($response->headers->get('X-Content-Security-Policy'));
        }

        return $headers;
    }

    /**
     * Converts a directive set array into Content-Security-Policy header.
     */
    public static function generateCspHeader(array $directives): string
    {
        return array_reduce(array_keys($directives), fn ($res, $name) => ('' !== $res ? $res.'; ' : '').sprintf('%s %s', $name, implode(' ', $directives[$name])), '');
    }

    private static function parseDirectives(string $header): array
    {
        $directives = [];

        foreach (explode(';', $header) as $directive) {
            $parts = explode(' ', trim($directive));
            if (\count($parts) < 1) {
                continue;
            }
            $name = array_shift($parts);
            $directives[$name] = $parts;
        }

        return $directives;
    }
}
