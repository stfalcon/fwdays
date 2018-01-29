<?php

namespace Application\Bundle\DefaultBundle\EventListener;

use JMS\I18nRoutingBundle\Router\I18nRouter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Class LocaleUrlRequestListener.
 */
class LocaleUrlResponseListener
{
    /**
     * @var
     */
    private $defaultLocale;
    /**
     * @var array
     */
    private $locales;
    /**
     * @var
     */
    private $cookieName;
    /** @var I18nRouter */
    private $routerService;

    /**
     * LocaleUrlResponseListener constructor.
     *
     * @param $defaultLocale
     * @param array $locales
     * @param $cookieName
     * @param $routerService
     */
    public function __construct($defaultLocale, array $locales, $cookieName, $routerService)
    {
        $this->defaultLocale = $defaultLocale;
        $this->locales = $locales;
        $this->cookieName = $cookieName;
        $this->routerService = $routerService;
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
            $event->setResponse(new RedirectResponse($request->getBaseUrl().'/'.$locale.($params ? '?'.http_build_query($params) : ''), 301));
        } elseif ('admin' === $currentLocal && $locale !== $this->defaultLocale) {
            $params = $request->query->all();
            unset($params[$this->cookieName]);
            $request->setLocale($this->defaultLocale);
            $event->setResponse(new RedirectResponse($request->getBaseUrl().$path.($params ? '?'.http_build_query($params) : ''), 301));
        } elseif (!in_array($currentLocal, $this->locales)) {
            try {
                $matched = $this->routerService->match('/'.$locale.$path);
            } catch (ResourceNotFoundException $e) {
                $matched = false;
            }
            if (false != $matched) {
                $params = $request->query->all();
                $event->setResponse(new RedirectResponse($request->getBaseUrl().'/'.$locale.$path.($params ? '?'.http_build_query($params) : ''), 301));
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

        if (in_array($currentLocal, $this->locales)) {
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
        return $request->cookies->has($this->cookieName) && in_array($request->cookies->get($this->cookieName), $this->locales)
            ? $request->cookies->get($this->cookieName) : $this->defaultLocale;
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
}
