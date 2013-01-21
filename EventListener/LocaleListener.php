<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\AccessMapInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author net working AG <info@networking.ch>
 */
class LocaleListener implements EventSubscriberInterface
{
    /**
     * @var string $router
     */
    private $router;

    /**
     * @var string $defaultLocale
     */
    private $defaultLocale;

    /**
     * @var \Symfony\Component\Security\Http\AccessMapInterface $accessMap
     */
    private $accessMap;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContext $securityContext
     */
    private $securityContext;

    /**
     * @var array $availableLanguages;
     */
    private $availableLanguages;


    /**
     * @param \Symfony\Component\Security\Http\AccessMapInterface $accessMap
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext
     * @param array $availableLanguages
     * @param string $defaultLocale
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    public function __construct(
        AccessMapInterface $accessMap,
        SecurityContext $securityContext,
        array $availableLanguages,
        $defaultLocale = 'en',
        RouterInterface $router = null
    ){
        $this->accessMap = $accessMap;
        $this->securityContext = $securityContext;
        $this->availableLanguages = $availableLanguages;
        $this->defaultLocale = $defaultLocale;
        $this->router = $router;
    }

    /**
     * @static
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            // must be registered after the Router to have access to the _locale
            KernelEvents::REQUEST => array(array('onKernelRequest', 16)),
        );
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->getSession()) {
            return;
        }

        if (!$request) {
            return;
        }

        $patterns = $this->accessMap->getPatterns($request);

        //@todo find a better solution to know if we are in the admin area or not
        $localeType = (in_array('ROLE_ADMIN', $patterns[0])) ? 'admin/_locale' : '_locale';


        if ($request->hasPreviousSession()) {
            $request->setDefaultLocale($request->getSession()->get($localeType, $this->defaultLocale));
        } else {
            $preferredLocale = $this->getPreferredLocale($request);
            $request->getSession()->set($localeType, $preferredLocale);
            $request->setDefaultLocale($preferredLocale);
        }


        if ($locale = $request->attributes->get('_locale')) {

            $request->setLocale($locale);

            if ($request->hasPreviousSession()) {

                $request->getSession()->set($localeType, $request->getLocale());
            }
        } else {
            $request->setLocale($request->getSession()->get($localeType));
        }

        if (null !== $this->router) {

            $this->router->getContext()->setParameter($localeType, $request->getLocale());
        }
    }


    /**
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $request = $event->getRequest();
        if (!$locale = $event->getAuthenticationToken()->getUser()->getLocale()) {
            $locale = $this->defaultLocale;
        }

        $request->getSession()->set('admin/_locale', $locale);

        $frontendLocale = $this->guessFrontendLocale($locale);

        $request->getSession()->set('_locale', $frontendLocale);
    }

    /**
     * guess frontend locale
     * @param mixed $locales
     * @return string
     */
    protected function guessFrontendLocale($locales)
    {
        // search for match in array
        if (is_array($locales)) {
            foreach ($locales as $locale) {
                $match = $this->matchLocaleInAvailableLanguages($locale);
                if (strlen($match) > 0) {
                    return $match;
                }
            }
        } // check if locale matches in available languages
        else {
            $match = $this->matchLocaleInAvailableLanguages($locales);
            if (strlen($match) > 0) {
                return $match;
            }
        }

        return $this->defaultLocale;
    }

    /**
     * @param $locale
     * @return string
     */
    protected function matchLocaleInAvailableLanguages($locale)
    {
        foreach ($this->availableLanguages as $language) {
            if (strpos($language['locale'], $locale, 0)) {
                return $language['locale'];
            }
            if (strpos($language['locale'], substr($locale, 0, 2), 0)) {
                return $language['locale'];
            }
        }

        return false;
    }


    /**
     * get preferred locale
     * @param \Symfony\Component\HttpFoundation\Request $request
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
     * get browser accept languages
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     */
    public function getBrowserAcceptLanguages(Request $request)
    {
        $browserLanguages = array();

        if (strlen($request->server->get('HTTP_ACCEPT_LANGUAGE')) == 0) {
            return array();
        }

        $languages = $this->splitHttpAcceptHeader(
            $request->server->get('HTTP_ACCEPT_LANGUAGE')
        );
        foreach ($languages as $lang) {
            if (strstr($lang, '-')) {
                $codes = explode('-', $lang);
                if ($codes[0] == 'i') {
                    // Language not listed in ISO 639 that are not variants
                    // of any listed language, which can be registered with the
                    // i-prefix, such as i-cherokee
                    if (count($codes) > 1) {
                        $lang = $codes[1];
                    }
                } else {
                    for ($i = 0, $max = count($codes); $i < $max; $i++) {
                        if ($i == 0) {
                            $lang = strtolower($codes[0]);
                        } else {
                            $lang .= '_' . strtoupper($codes[$i]);
                        }
                    }
                }
            }

            $browserLanguages[] = $lang;
        }

        return $browserLanguages;
    }

    /**
     * split http accept header
     * @param string $header
     * @return array
     */
    public function splitHttpAcceptHeader($header)
    {
        $values = array();
        foreach (array_filter(explode(',', $header)) as $value) {
            // Cut off any q-value that might come after a semi-colon
            if ($pos = strpos($value, ';')) {
                $q = (float)trim(substr($value, strpos($value, '=') + 1));
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
