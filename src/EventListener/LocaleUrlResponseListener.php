<?php

namespace App\EventListener;

use Maxmind\Bundle\GeoipBundle\Service\GeoipManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
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
    private $router;
    /** @var array */
    private $pathArray = [];
    /** @var array */
    private $skipRoutes = [];

    /**
     * @param string       $locale
     * @param array        $locales
     * @param string       $localeCookieName
     * @param GeoipManager $geoIpService
     * @param Router       $router
     */
    public function __construct(string $locale, array $locales, string $localeCookieName, GeoipManager $geoIpService, Router $router)
    {
        $this->defaultLocale = $locale;
        $this->locales = $locales;
        $this->cookieName = $localeCookieName;
        $this->geoIpService = $geoIpService;
        $this->router = $router;
        $this->skipRoutes[] = $this->router->generate('payment_service_interaction', ['_locale' => 'uk']);
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
     * @param string $url
     *
     * @return RedirectResponse
     */
    private function createResponseWithCheckCookie(string $url): RedirectResponse
    {
        return new RedirectResponse($url, self::REDIRECT_NUMBER);
    }
}
