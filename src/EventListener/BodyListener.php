<?php

namespace Networking\InitCmsBundle\EventListener;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BodyListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $contentType = $request->headers->get('Content-Type');

        if ($this->isDecodeable($request)) {
            $format = null === $contentType
                ? $request->getRequestFormat()
                : $request->getFormat($contentType);

            $format = $format ?: 'json';

            $content = $request->getContent();

            if ('json' !== $format) {
                return;
            }

            if (!empty($content)) {
                $data = $this->decode($content);
                if (is_array($data)) {
                    $request->request = new ParameterBag($data);
                } else {
                    throw new BadRequestHttpException('Invalid '.$format.' message received');
                }
            }
        }
    }

    private function decode(string $data): mixed
    {
        return @json_decode($data, true);
    }

    private function isDecodeable(Request $request): bool
    {
        if (!in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return false;
        }

        return !$this->isFormRequest($request);
    }

    private function isFormRequest(Request $request): bool
    {
        $contentTypeParts = explode(';', $request->headers->get('Content-Type', ''));

        if (isset($contentTypeParts[0])) {
            return in_array($contentTypeParts[0], ['multipart/form-data', 'application/x-www-form-urlencoded']);
        }

        return false;
    }
}
