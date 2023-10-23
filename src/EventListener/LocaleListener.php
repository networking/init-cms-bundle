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
namespace Networking\InitCmsBundle\EventListener;

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\AccessMapInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LocaleListener.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class LocaleListener
{
    /**
     * @var string
     */
    protected $router;

    /**
     * @var \Symfony\Component\Security\Http\AccessMapInterface
     */
    protected $accessMap;

    /**
     * @var array;
     */
    protected $availableLanguages;

    /**
     * LocaleListener constructor.
     *
     * @param string               $defaultLocale
     * @param RouterInterface|null $router
     * @param bool                 $allowLocaleCookie
     * @param bool                 $singleLanguage
     */
    public function __construct(
        AccessMapInterface $accessMap,
        array $availableLanguages,
        protected bool $allowLocaleCookie,
        protected bool $singleLanguage,
        protected string $defaultLocale = 'en',
        RouterInterface $router = null

    ) {
        $this->accessMap = $accessMap;
        $this->availableLanguages = $availableLanguages;
        $this->router = $router;

        $env = [];

        if(false === getenv('ALLOW_LOCALE_COOKIE')){
	        $env['ALLOW_LOCALE_COOKIE'] = $this->allowLocaleCookie;
            putenv(sprintf('ALLOW_LOCALE_COOKIE=%s', $this->allowLocaleCookie) );
        }

	    if(false === getenv('SINGLE_LANGUAGE')){
		    $env['SINGLE_LANGUAGE'] = $this->singleLanguage;
            putenv(sprintf('SINGLE_LANGUAGE=%s', $this->singleLanguage) );
	    }
	    if(count($env)){
		    (new Dotenv())->populate($env, true);
	    }
    }

    /**
     * @return void
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        if (!$request) {
            return;
        }

        $patterns = $this->accessMap->getPatterns($request);
        //@todo find a better solution to know if we are in the admin area or not
        $localeType = (in_array('ROLE_SONATA_ADMIN', $patterns[0])) ? 'admin/_locale' : '_locale';
        if ($localeType == 'admin/_locale') {
            $locale = $request->getSession()->get($localeType);
        } else {
            if ($this->singleLanguage) {
                $locale = $this->defaultLocale;
            } elseif ($this->allowLocaleCookie) {
                $locale = $request->cookies->get($localeType, false);

                //fallback if browser does not support samesite=none
                if(!$locale){
                    $locale = $request->cookies->get($localeType.'_legacy', false);
                }
            } else {
                $locale = $this->getLocaleFromUrl($request->getPathInfo());
            }
        }

        if (!$locale && $request->query->get('_locale')) {
            $locale = $request->query->get('_locale');
        }

        /*
         * handle locale:
         * 1. priority: defined in cookie: set request attribute as symfony will set request->setLocale
         * 2. priority: defined in browser or default: set request attribute as symfony will set request->setLocale
         */
        if ($locale) {
            // there is a session -> use that
            $request->setLocale($locale);
        } else {
            // nothing set, get the preferred locale and use it
            $preferredLocale = $this->getPreferredLocale($request);
            $request->setLocale($preferredLocale);
        }

        if (null !== $this->router) {
            $this->router->getContext()->setParameter($localeType, $request->getLocale());
        }
    }

    /**
     * @param $url
     *
     * @return bool|mixed
     */
    protected function getLocaleFromUrl($url)
    {
        $parts = explode('/', (string) $url);
        $locale = $parts[1];

        foreach ($this->availableLanguages as $language) {
            if ($locale === substr((string) $language['locale'], 0, 2)) {
                return $language['locale'];
            }
        }

        return false;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $request = $event->getRequest();
        if (!$locale = $event->getAuthenticationToken()->getUser()->getLocale()) {
            $locale = $this->defaultLocale;
        }

        $patterns = $this->accessMap->getPatterns($request);

        //Set backend language to exactly user language settings (if it exists or not)
        $request->getSession()->set('admin/_locale', $locale);

        // If user language does not exist in frontend website, get next best
        $frontendLocale = $this->guessFrontendLocale($locale);

        if (in_array('ROLE_SONATA_ADMIN', $patterns[0])) {
            $request->setLocale($locale);
        } else {
            $request->setLocale($frontendLocale);
        }
    }

    /**
     * guess frontend locale.
     *
     *
     * @return string
     */
    protected function guessFrontendLocale(mixed $locales)
    {
        // search for match in array
        if (is_array($locales)) {
            foreach ($locales as $locale) {
                $match = $this->matchLocaleInAvailableLanguages($locale);
                if ($match && strlen($match) > 0) {
                    return $match;
                }
            }
        } // check if locale matches in available languages
        else {
            $match = $this->matchLocaleInAvailableLanguages($locales);
            if ($match && strlen($match) > 0) {
                return $match;
            }
        }

        return $this->defaultLocale;
    }

    /**
     * try to match browser language with available languages.
     *
     * @param $locale
     *
     * @return string
     */
    protected function matchLocaleInAvailableLanguages($locale)
    {
        foreach ($this->availableLanguages as $language) {
            // browser accept language matches an available language
            if ($locale == $language['locale']) {
                return $language['locale'];
            }
            // first part of browser accept language matches an available language
            if (substr((string) $locale, 0, 2) == substr((string) $language['locale'], 0, 2)) {
                return $language['locale'];
            }
        }

        return false;
    }

    /**
     * get preferred locale.
     *
     *
     * @return string
     */
    public function getPreferredLocale(Request $request)
    {
        $browserAcceptLanguages = $this->getBrowserAcceptLanguages($request);
        if (empty($browserAcceptLanguages)) {
            return $this->defaultLocale;
        }

        return $this->guessFrontendLocale($browserAcceptLanguages);
    }

    /**
     * get browser accept languages.
     *
     *
     * @return array
     */
    public function getBrowserAcceptLanguages(Request $request)
    {
        $browserLanguages = [];

        if (strlen((string) $request->server->get('HTTP_ACCEPT_LANGUAGE')) == 0) {
            return [];
        }

        $languages = $this->splitHttpAcceptHeader(
            $request->server->get('HTTP_ACCEPT_LANGUAGE')
        );
        foreach ($languages as $lang) {
            if (strstr((string) $lang, '-')) {
                $codes = explode('-', (string) $lang);
                if ($codes[0] == 'i') {
                    // Language not listed in ISO 639 that are not variants
                    // of any listed language, which can be registered with the
                    // i-prefix, such as i-cherokee
                    if (count($codes) > 1) {
                        $lang = $codes[1];
                    }
                } else {
                    for ($i = 0, $max = count($codes); $i < $max; ++$i) {
                        if ($i == 0) {
                            $lang = strtolower($codes[0]);
                        } else {
                            $lang .= '_'.strtoupper($codes[$i]);
                        }
                    }
                }
            }

            $browserLanguages[] = $lang;
        }

        return $browserLanguages;
    }

    /**
     * split http accept header.
     *
     * @param string $header
     */
    public function splitHttpAcceptHeader($header): array
    {
        $values = [];
        foreach (array_filter(explode(',', $header)) as $value) {
            // Cut off any q-value that might come after a semi-colon
            if ($pos = strpos($value, ';')) {
                $q = (float) trim(substr($value, strpos($value, '=') + 1));
                $value = substr($value, 0, $pos);
            } else {
                $q = 1;
            }

            if (0 < $q) {
                $values[trim($value)] = $q;
            }
        }

        arsort($values);

        return array_keys($values);
    }
}
