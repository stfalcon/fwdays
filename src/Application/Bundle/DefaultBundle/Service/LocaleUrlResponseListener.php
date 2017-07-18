<?php

namespace Application\Bundle\DefaultBundle\Service;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Class LocaleUrlRequestListener
 *
 * @package AppBundle\Service
 */
class LocaleUrlResponseListener
{
    private $defaultLocale;
    private $locales;
    private $cookieName;

    public function __construct($defaultLocale, array $locales, $cookieName)
    {
        $this->defaultLocale = $defaultLocale;
        $this->locales = $locales;
        $this->cookieName = $cookieName;
    }

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
        $locale = $request->cookies->has($this->cookieName) && in_array($request->cookies->get($this->cookieName), $this->locales)
            ? $request->cookies->get($this->cookieName) : $this->defaultLocale;

        $path = $request->getPathInfo();
        $currentLocal = $this->getInnerSubstring($path,'/');
        if ('' === rtrim($path, '/')) {
            $params = $request->query->all();
            unset($params['hl']);
            $event->setResponse(new RedirectResponse($request->getBaseUrl() . '/' . $locale . '/' . ($params ? '?' . http_build_query($params) : ''), 301));

        } elseif (!in_array($currentLocal, $this->locales)) {
            $params = $request->query->all();
            unset($params['hl']);
            $event->setResponse(new RedirectResponse($request->getBaseUrl() . '/' . $locale . $path . ($params ? '?' . http_build_query($params) : ''), 301));

        } elseif (in_array($currentLocal, $this->locales) && 'admin' === $this->getInnerSubstring($path,'/', 2)) {
            $params = $request->query->all();
            unset($params['hl']);
            $path = ltrim($path, '/'.$currentLocal);
            $event->setResponse(new RedirectResponse($request->getBaseUrl() . '/' . $path . ($params ? '?' . http_build_query($params) : ''), 301));
        }
    }

    private function getInnerSubstring($string, $delim, $KeyNumber = 1)
    {
        $string = explode($delim, $string, 3);

        return isset($string[$KeyNumber]) ? $string[$KeyNumber] : '';
    }
}