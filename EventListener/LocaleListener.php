<?php

/*
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
     * @param string $defaultLocale
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    public function __construct(AccessMapInterface $accessMap, SecurityContext $securityContext, array $availableLanguages, $defaultLocale = 'en', RouterInterface $router = null)
    {
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

        if (!$request->getSession()) return;

        if (!$request) return;

        $patterns = $this->accessMap->getPatterns($request);

        //@todo find a better solution to know if we are in the admin area or not
        $localeType = (in_array('ROLE_ADMIN', $patterns[0])) ? 'admin/_locale' : '_locale';


        if ($request->hasPreviousSession()) {
            $request->setDefaultLocale($request->getSession()->get($localeType, $this->defaultLocale));
        } else {
            $request->setDefaultLocale($this->defaultLocale);
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
     * @param $locale
     * @return string
     */
    protected function guessFrontendLocale($locale)
    {
        foreach ($this->availableLanguages as $language) {
            if (strpos($language['locale'], $locale, 0)) return $language['locale'];
            if (strpos($language['locale'], substr($locale, 0, 2), 0)) return $language['locale'];
        }
        return $this->defaultLocale;
    }
}
