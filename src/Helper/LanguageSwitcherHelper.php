<?php

declare(strict_types=1);

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Networking\InitCmsBundle\Component\Routing\Route;
use Networking\InitCmsBundle\Entity\ContentRoute;
use Networking\InitCmsBundle\Entity\ContentRouteManager;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Networking\InitCmsBundle\Model\PageSnapshotInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class LanguageSwitcherHelper.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class LanguageSwitcherHelper
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var PageManagerInterface
     */
    protected $pageManager;

    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var PageHelper
     */
    protected $pageHelper;

    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $om,
        PageHelper $pageHelper,
        PageManagerInterface $pageManager,
        RouterInterface $router,
        protected string $fallbackRoute
    ) {
        $this->requestStack = $requestStack;
        $this->om = $om;
        $this->pageHelper = $pageHelper;
        $this->pageManager = $pageManager;
        $this->router = $router;
    }

    /**
     * Returns the corresponding route of the given URL for the locale supplied
     * If none is found it returns the original route object.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getTranslationRoute($oldUrl, $oldLocale, $locale): array|Route|RouteObjectInterface|string
    {
        $cookies = $this->requestStack->getCurrentRequest()->cookies ? $this->requestStack->getCurrentRequest(
        )->cookies->all() : [];
        $oldRequest = Request::create($oldUrl, 'GET', [], $cookies);
        $oldRequest->setLocale($oldLocale);

        if ($this->requestStack->getCurrentRequest()->getSession()) {
            $oldRequest->setSession($this->requestStack->getCurrentRequest()->getSession());
        }

        try {
            $request = $this->pageHelper->matchContentRouteRequest($oldRequest);
        } catch (ResourceNotFoundException) {
            $request = $oldRequest;
        }

        if (!$content = $request->get('_content', false)) {
            try {
                $route = $this->router->matchRequest(Request::create($oldUrl));
            } catch (ResourceNotFoundException) {
                if ($route = $this->router->matchRequest(Request::create('/404'))) {
                    return $route;
                }
                throw new NotFoundHttpException(sprintf('Could not find a translation to "%s" for this request"', $locale));
            }

            if (!array_key_exists('_content', $route)) {
                return $route;
            }

            if (!$content = $route['_content']) {
                return $route;
            }
        }

        $data = match (true) {
            $content instanceof PageInterface => [
                'translations' => $content->convertTranslationsToArray(),
                'originals' => $content->convertOriginalsToArray(),
            ],
            $content instanceof PageSnapshotInterface => json_decode($content->getVersionedData(), true),
            default => ['translations' => [], 'originals' => []],
        };

        $route = $this->extractTranslationForLocale($data['translations'], $locale);

        if ($route instanceof RouteObjectInterface) {
            return $route;
        }

        $route = $this->extractTranslationForLocale($data['originals'], $locale);

        if ($route instanceof RouteObjectInterface) {
            return $route;
        }

        if ($this->fallbackRoute) {
            return $this->fallbackRoute;
        }

        if ($route = $this->router->matchRequest(Request::create('/404'))) {
            return $route;
        }

        // no valid translation found
        throw new NotFoundHttpException(sprintf('Could not find a translation to "%s" for content "%s"', $locale, $oldUrl));
    }

    public function extractTranslationForLocale(array $translations, string $locale): ?RouteObjectInterface
    {
        foreach ($translations as $translation) {
            if (is_array($translation) && $translation['locale'] === $locale) {
                return $this->generateRouteFromTranslationArray($translation);
            }

            if (is_int($translation)) {
                return $this->generateRouteFromTranslationId($translation, $locale);
            }
        }

        return null;
    }

    public function generateRouteFromTranslationArray(array $translationArray): RouteObjectInterface
    {
        $contentRouteData = $translationArray['contentRoute'];
        $contentRoute = $this->om->getRepository(ContentRoute::class)->find($contentRouteData['id']);

        return ContentRouteManager::generateRoute($contentRoute, $contentRouteData['path'], '');
    }

    protected function generateRouteFromTranslationId(int $id, string $locale): ?RouteObjectInterface
    {
        /** @var PageInterface $page */
        $page = $this->pageManager->findOneBy(['id' => $id, 'locale' => $locale]);

        if ($page) {
            /** @var PageSnapshotInterface $snapshot */
            $snapshot = $page->getSnapshot();

            return ContentRouteManager::generateRoute($snapshot->getContentRoute(), $snapshot->getPath(), '');
        }

        $page = $this->pageManager->findOneBy(['id' => $id]);

        $translation = $page->getAllTranslations()->filter(
            function (PageInterface $page) use ($locale) {
                return $page->getLocale() === $locale;
            }
        )->first();

        if ($translation) {
            /** @var PageSnapshotInterface $snapshot */
            $snapshot = $translation->getSnapshot();

            return ContentRouteManager::generateRoute($snapshot->getContentRoute(), $snapshot->getPath(), '');
        }

        return null;
    }

    /**
     * Get the query string parameters for the current request
     * Not used at present.
     */
    public function getQueryString(): ?string
    {
        $qs = Request::normalizeQueryString($this->requestStack->getCurrentRequest()->server->get('QUERY_STRING'));

        return '' === $qs ? null : $qs;
    }

    /**
     * Returns the uri Path for the current uri if no referrer given,
     * otherwise it returns the path for the URL given.
     *
     * @param null $referrer
     */
    public function getPathInfo($referrer = null): ?string
    {
        $baseUrl = $this->prepareBaseUrl($referrer);

        if (null === $referrer) {
            return '/';
        }

        $pathInfo = '/';

        // Remove the query string from REQUEST_URI
        if ($pos = strpos($referrer, '?')) {
            $referrer = substr($referrer, 0, $pos);
        }

        $host = $this->requestStack->getCurrentRequest()->getHost();
        if ((null !== $baseUrl) && (false === ($pathInfo = substr($referrer, strlen($baseUrl))))) {
            // If substr() returns false then PATH_INFO is set to an empty string
            return '/';
        } elseif (null === $baseUrl) {
            return $referrer;
        } elseif ($pos = strpos((string) $pathInfo, (string) $host)) {
            $pathInfo = substr((string) $pathInfo, $pos + strlen((string) $host));
        }

        return (string) $pathInfo;
    }

    /**
     * Prepares the Base URL.
     */
    public function prepareBaseUrl($referrer): bool|string
    {
        $filename = basename((string) $this->requestStack->getCurrentRequest()->server->get('SCRIPT_FILENAME'));

        if (basename((string) $this->requestStack->getCurrentRequest()->server->get('SCRIPT_NAME')) === $filename) {
            $baseUrl = $this->requestStack->getCurrentRequest()->server->get('SCRIPT_NAME');
        } elseif (basename((string) $this->requestStack->getCurrentRequest()->server->get('PHP_SELF')) === $filename) {
            $baseUrl = $this->requestStack->getCurrentRequest()->server->get('PHP_SELF');
        } elseif (basename((string) $this->requestStack->getCurrentRequest()->server->get('ORIG_SCRIPT_NAME')) === $filename) {
            $baseUrl = $this->requestStack->getCurrentRequest()->server->get(
                'ORIG_SCRIPT_NAME'
            ); // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path = $this->requestStack->getCurrentRequest()->server->get('PHP_SELF', '');
            $file = $this->requestStack->getCurrentRequest()->server->get('SCRIPT_FILENAME', '');
            $segs = explode('/', trim((string) $file, '/'));
            $segs = array_reverse($segs);
            $index = 0;
            $last = count($segs);
            $baseUrl = '';
            do {
                $seg = $segs[$index];
                $baseUrl = '/'.$seg.$baseUrl;
                ++$index;
            } while (($last > $index) && (false !== ($pos = strpos((string) $path, $baseUrl))) && (0 != $pos));
        }

        // Does the baseUrl have anything in common with the request_uri?
        $requestUri = $referrer;

        if ($baseUrl && $prefix = $this->getUrlencodedPrefix($requestUri, $baseUrl)) {
            // full $baseUrl matches
            return $prefix;
        }

        if ($baseUrl && $prefix = $this->getUrlencodedPrefix($requestUri, dirname((string) $baseUrl))) {
            // directory portion of $baseUrl matches
            return rtrim($prefix, '/');
        }

        $truncatedRequestUri = $requestUri;
        if (($pos = strpos((string) $requestUri, '?')) !== false) {
            $truncatedRequestUri = substr((string) $requestUri, 0, $pos);
        }

        $basename = basename((string) $baseUrl);

        if (empty($basename) || !strpos(rawurldecode((string) $truncatedRequestUri), $basename)) {
            // no match whatsoever; set it blank
            return '';
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseUrl. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        if ((strlen((string) $requestUri) >= strlen((string) $baseUrl)) && ((false !== ($pos = strpos(
            (string) $requestUri,
            (string) $baseUrl
        ))) && (0 !== $pos))) {
            $baseUrl = substr((string) $requestUri, 0, $pos + strlen((string) $baseUrl));
        }

        return rtrim((string) $baseUrl, '/');
    }

    /*
     * Returns the prefix as encoded in the string when the string starts with
     * the given prefix, false otherwise.
     *
     * @param string $string The urlencoded string
     * @param string $prefix The prefix not encoded
     *
     * @return string|false The prefix as it is encoded in $string, or false
     */
    protected function getUrlencodedPrefix($string, $prefix): ?string
    {
        if (!str_starts_with(rawurldecode((string) $string), (string) $prefix)) {
            return null;
        }

        $len = strlen((string) $prefix);

        if (preg_match("#^(%[[:xdigit:]]{2}|.){{$len}}#", (string) $string, $match)) {
            return $match[0];
        }

        return null;
    }
}
