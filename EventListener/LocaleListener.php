<?php
namespace Networking\InitCmsBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocaleListener implements EventSubscriberInterface
{
    private $router;
    private $defaultLocale;
    private $accessMap;

    public function setAccessMap(\Symfony\Component\Security\Http\AccessMapInterface $accessMap )
    {
        $this->accessMap = $accessMap;
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
     * @param string                                     $defaultLocale
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    public function __construct($defaultLocale = 'en', RouterInterface $router = null)
    {
        $this->defaultLocale = $defaultLocale;
        $this->router = $router;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if(!$request->getSession()) return;

        if(!$request) return;

        $patterns = $this->accessMap->getPatterns($request);

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
}
