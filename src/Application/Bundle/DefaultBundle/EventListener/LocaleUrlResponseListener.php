<?php

namespace Application\Bundle\DefaultBundle\EventListener;

use JMS\I18nRoutingBundle\Router\I18nRouter;
use Maxmind\Bundle\GeoipBundle\Service\GeoipManager;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Router;

/**
 * Class LocaleUrlRequestListener.
 */
class LocaleUrlResponseListener
{
    const UKRAINE_COUNTRY_CODE = 'UA';

    private $defaultLocale;

    private $locales;

    private $cookieName;

    /** @var I18nRouter */
    private $routerService;

    private $geoIpService;

    /** @var Logger */
    private $logger;

    /**
     * LocaleUrlResponseListener constructor.
     *
     * @param string       $defaultLocale
     * @param array        $locales
     * @param string       $cookieName
     * @param Router       $routerService
     * @param GeoipManager $geoIpService
     * @param Logger       $logger
     */
    public function __construct($defaultLocale, array $locales, $cookieName, $routerService, $geoIpService, $logger)
    {
        $this->defaultLocale = $defaultLocale;
        $this->locales = $locales;
        $this->cookieName = $cookieName;
        $this->routerService = $routerService;
        $this->geoIpService = $geoIpService;
        $this->logger = $logger;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $locale = $this->getCurrentLocale($request);

        if ($locale === $this->defaultLocale) {
            $request->setLocale($locale);

            return;
        }

        $path = $request->getPathInfo();
        $currentLocal = $this->getInnerSubstring($path, '/');
        if ('' === rtrim($path, '/')) {
            $params = $request->query->all();
            $event->setResponse(new RedirectResponse($request->getBaseUrl().'/'.$locale.($params ? '?'.http_build_query($params) : '/'), 301));
        } elseif ('admin' === $currentLocal && $locale !== $this->defaultLocale) {
            $params = $request->query->all();
            unset($params[$this->cookieName]);
            $request->setLocale($this->defaultLocale);
            $event->setResponse(new RedirectResponse($request->getBaseUrl().$path.($params ? '?'.http_build_query($params) : '/'), 301));
        } elseif (!in_array($currentLocal, $this->locales, true)) {
            try {
                $matched = $this->routerService->match('/'.$locale.$path);
            } catch (ResourceNotFoundException $e) {
                $matched = false;
            }
            if (false !== $matched) {
                $params = $request->query->all();
                $event->setResponse(new RedirectResponse($request->getBaseUrl().'/'.$locale.$path.($params ? '?'.http_build_query($params) : '/'), 301));
            }
        }
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $ex = $event->getException();
        if (!$ex instanceof NotFoundHttpException || !$ex->getPrevious() instanceof ResourceNotFoundException) {
            return;
        }

        $request = $event->getRequest();

        $path = $request->getPathInfo();
        $currentLocal = $this->getInnerSubstring($path, '/');

        if (in_array($currentLocal, $this->locales, true)) {
            $request->setLocale($currentLocal);
        }

        if ($currentLocal === $this->defaultLocale) {
            $params = $request->query->all();
            unset($params[$this->cookieName]);
            $path = ltrim($path, '/'.$currentLocal);
            $event->setResponse(new RedirectResponse($request->getBaseUrl().'/'.$path.($params ? '?'.http_build_query($params) : ''), 301));
        }
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    private function getCurrentLocale($request)
    {
        $local = null;

        // get local from cookie
        if ($request instanceof Request) {
            if ($request->cookies->has($this->cookieName)
                && in_array($request->cookies->get($this->cookieName), $this->locales, true)) {
                $local = $request->cookies->get($this->cookieName);
            }
        }

        if (!$local) {
            if (false !== $this->geoIpService->lookup($this->getRealIpAddr($request))) {
                if (self::UKRAINE_COUNTRY_CODE === $this->geoIpService->getCountryCode()) {
                    $local = $this->defaultLocale;
                }
            }
        }

        // get locale from preferred languages
        if (!$local) {
            $local = $request->getPreferredLanguage($this->locales);
        }

        return $local;
    }

    /**
     * @param string $string
     * @param string $delim
     * @param int    $keyNumber
     *
     * @return string
     */
    private function getInnerSubstring($string, $delim, $keyNumber = 1)
    {
        $string = explode($delim, $string, 3);

        return isset($string[$keyNumber]) ? $string[$keyNumber] : '';
    }

    /**
     * @param Request $request
     *
     * @return null|string
     */
    private function getRealIpAddr($request)
    {
        $server = $request->server;
        if (!$server) {
            return null;
        }
        $ip = null;
        if ($server->has('HTTP_CLIENT_IP')) {
            $ip = filter_var($server->get('HTTP_CLIENT_IP'), FILTER_VALIDATE_IP);
        }

        if (!$ip && $server->has('HTTP_X_FORWARDED_FOR')) {
            $ip = filter_var($server->get('HTTP_X_FORWARDED_FOR'), FILTER_VALIDATE_IP);
        }

        if (!$ip) {
            $ip = $server->get('REMOTE_ADDR');
        }

        return $ip;
    }
}
