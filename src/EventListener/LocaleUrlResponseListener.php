<?php

namespace App\EventListener;

use App\Traits\RouterTrait;
use Maxmind\Bundle\GeoipBundle\Service\GeoipManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ServerBag;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * LocaleUrlRequestListener.
 */
class LocaleUrlResponseListener implements EventSubscriberInterface
{
    use RouterTrait;

    private const UKRAINE_COUNTRY_CODE = 'UA';
    private const LANG_FROM_COOKIE = 'lang_from_cookie';
    private const LANG_FROM_IP = 'lang_from_ip';
    private const LANG_FROM_PREFERRED = 'lang_from_preferred';
    private const LANG_FROM_NULL = 'lang_from_null';
    private const REDIRECT_NUMBER = 302;

    private $defaultLocale;
    private $locales;
    private $cookieName;
    private $geoIpService;
    /** @var array */
    private $pathArray = [];

    /**
     * @param string       $locale
     * @param array        $locales
     * @param string       $localeCookieName
     * @param GeoipManager $geoIpService
     */
    public function __construct(string $locale, array $locales, string $localeCookieName, GeoipManager $geoIpService)
    {
        $this->defaultLocale = $locale;
        $this->locales = $locales;
        $this->cookieName = $localeCookieName;
        $this->geoIpService = $geoIpService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): \Generator
    {
        yield KernelEvents::REQUEST => 'onKernelRequest';
        yield KernelEvents::EXCEPTION => 'onKernelException';
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $langSource = self::LANG_FROM_NULL;
        $locale = $this->getCurrentLocale($request, $langSource);

        $path = $request->getPathInfo();
        $pathLocal = $this->getInnerSubstring($path, '/');

        if ($locale === $this->defaultLocale && '' === $pathLocal) {
            $request->setLocale($locale);

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
                $matched = $this->router->match('/'.$locale.$path);
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
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $ex = $event->getThrowable();
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
    private function getCurrentLocale(Request $request, string &$langSource)
    {
        $local = null;

        if ($request->cookies->has($this->cookieName) &&
            \in_array($request->cookies->get($this->cookieName), $this->locales, true)) {
            $local = $request->cookies->get($this->cookieName);
            $langSource = self::LANG_FROM_COOKIE;
        }

        if (!$local) {
            if (false !== $this->geoIpService->lookup($request->getClientIp())) {
                if (self::UKRAINE_COUNTRY_CODE === $this->geoIpService->getCountryCode()) {
                    $local = $this->defaultLocale;
                    $langSource = self::LANG_FROM_IP;
                }
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
    private function getInnerSubstring(string $string, string $delim, int $keyNumber = 1): string
    {
        $array = \explode($delim, $string, 3);
        $this->pathArray = \is_array($array) ? $array : [];

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
        if (!$server instanceof ServerBag) {
            return null;
        }

        $ip = null;
        if ($server->has('HTTP_CLIENT_IP')) {
            $ip = \filter_var($server->get('HTTP_CLIENT_IP'), FILTER_VALIDATE_IP);
        }

        if (!$ip && $server->has('HTTP_X_FORWARDED_FOR')) {
            $ip = \filter_var($server->get('HTTP_X_FORWARDED_FOR'), FILTER_VALIDATE_IP);
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
        return new RedirectResponse($url, self::REDIRECT_NUMBER);
    }
}
