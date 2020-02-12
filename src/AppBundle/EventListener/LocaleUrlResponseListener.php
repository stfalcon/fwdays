<?php

namespace App\EventListener;

use JMS\I18nRoutingBundle\Router\I18nRouter;
use Maxmind\Bundle\GeoipBundle\Service\GeoipManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Class LocaleUrlRequestListener.
 */
class LocaleUrlResponseListener
{
    private const UKRAINE_COUNTRY_CODE = 'UA';
    private const LANG_FROM_COOKIE = 'lang_from_cookie';
    private const LANG_FROM_IP = 'lang_from_ip';
    private const LANG_FROM_PREFERRED = 'lang_from_preferred';
    private const LANG_FROM_NULL = 'lang_from_null';
    private const REDIRECT_NUMBER = 302;

    private $defaultLocale;
    private $locales;
    private $cookieName;
    private $routerService;
    private $geoIpService;
    private $pathArray = [];
    private $skipRoutes = [];


    /**
     * @param string       $defaultLocale
     * @param array        $locales
     * @param string       $cookieName
     * @param I18nRouter   $routerService
     * @param GeoipManager $geoIpService
     */
    public function __construct(string $defaultLocale, array $locales, string $cookieName, I18nRouter $routerService, GeoipManager $geoIpService)
    {
        $this->defaultLocale = $defaultLocale;
        $this->locales = $locales;
        $this->cookieName = $cookieName;
        $this->routerService = $routerService;
        $this->geoIpService = $geoIpService;
        $this->skipRoutes[] = $this->routerService->generate('payment_service_interaction', ['_locale' => 'uk']);
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
        $path = $request->getPathInfo();

        if (\in_array($path, $this->skipRoutes, true)) {
            $request->setLocale($this->defaultLocale);

            return;
        }

        $langSource = self::LANG_FROM_NULL;
        $locale = $this->getCurrentLocale($request, $langSource);
        $pathLocal = $this->getInnerSubstring($path, '/');

        if ($locale === $this->defaultLocale && '' === $pathLocal) {
            $request->setLocale($locale);

            return;
        }

        if ($this->defaultLocale !== $pathLocal && \in_array($pathLocal, $this->locales, true)) {
            $request->setLocale($pathLocal);

            return;
        }

        if ($pathLocal !== $locale && \in_array($pathLocal, $this->locales, true)) {
            $params = $request->query->all();
            $newPath = $this->getStringFromPathArray(2);
            $request->setLocale($locale);
            $response = $this->createResponseWithCheckCookie($request->getBaseUrl().'/'.$locale.$newPath.($params ? '?'.http_build_query($params) : ''));
            $event->setResponse($response);
        } elseif ('' === rtrim($path, '/')) {
            $params = $request->query->all();
            $response = $this->createResponseWithCheckCookie($request->getBaseUrl().'/'.$locale.($params ? '?'.http_build_query($params) : '/'));
            $event->setResponse($response);
        } elseif (!\in_array($pathLocal, $this->locales, true)) {
            try {
                $matched = $this->routerService->match('/'.$locale.$path);
            } catch (ResourceNotFoundException | MethodNotAllowedException $e) {
                $matched = false;
            }
            if (false !== $matched) {
                $params = $request->query->all();
                $response = $this->createResponseWithCheckCookie($request->getBaseUrl().'/'.$locale.$path.($params ? '?'.http_build_query($params) : ''));
                $event->setResponse($response);
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

        if (\in_array($currentLocal, $this->locales, true)) {
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
     * @param string  $langSource
     *
     * @return mixed
     */
    private function getCurrentLocale($request, string &$langSource)
    {
        $local = null;

        // get local from cookie
        if ($request instanceof Request) {
            if ($request->cookies->has($this->cookieName) &&
                \in_array($request->cookies->get($this->cookieName), $this->locales, true)) {
                $local = $request->cookies->get($this->cookieName);
                $langSource = self::LANG_FROM_COOKIE;
            }
        }

        if (!$local) {
            if (false !== $this->geoIpService->lookup($this->getRealIpAddr($request))) {
                if (self::UKRAINE_COUNTRY_CODE === $this->geoIpService->getCountryCode()) {
                    $local = $this->defaultLocale;
                } else {
                    $local = 'en';
                }
                $langSource = self::LANG_FROM_IP;
            }
        }

        // get locale from preferred languages
        if (!$local) {
            $local = $request->getPreferredLanguage($this->locales);
            $langSource = self::LANG_FROM_PREFERRED;
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
        $this->pathArray = explode($delim, $string, 3);

        return isset($this->pathArray[$keyNumber]) ? $this->pathArray[$keyNumber] : '';
    }

    /**
     * @param int $from
     *
     * @return string
     */
    private function getStringFromPathArray(int $from = 0): string
    {
        $result = '';
        for ($key = $from; isset($this->pathArray[$key]); ++$key) {
            $result .= '/'.$this->pathArray[$key];
        }

        return $result;
    }

    /**
     * @param Request $request
     *
     * @return string|null
     */
    private function getRealIpAddr($request): ?string
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

    /**
     * @param string $url
     *
     * @return RedirectResponse
     */
    private function createResponseWithCheckCookie(string $url): RedirectResponse
    {
        $response = new RedirectResponse($url, self::REDIRECT_NUMBER);

        return $response;
    }
}
