<?php

namespace App\EventListener;

use App\Entity\Referer\Referer;
use App\Service\RefererService;
use App\Traits\LoggerTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * RefererRequestSubscriber.
 */
class RefererRequestSubscriber implements EventSubscriberInterface
{
    use LoggerTrait;

    public const EXCLUDE_URLS = [
        '~^https://accounts.google.com/~',
    ];

    /** @var RefererService */
    private $refererService;

    /** @var string|null */
    private $newCookie = null;

    /**
     * @param RefererService $refererService
     */
    public function __construct(RefererService $refererService)
    {
        $this->refererService = $refererService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): \Generator
    {
        yield KernelEvents::REQUEST => ['onKernelRequest', 1];
        yield KernelEvents::RESPONSE => ['onKernelResponse', 1];
    }

    /**
     * @param ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ($request->isXmlHttpRequest()) {
            return;
        }

        if (\is_string($this->newCookie)) {
            $cookie = new Cookie(Referer::COOKIE_KEY, $this->newCookie, \time() + 365 * 24 * 60 * 60);
            $this->newCookie = null;
            $event->getResponse()->headers->setCookie($cookie);
        }
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

        if ($request->isXmlHttpRequest()) {
            return;
        }

        $refererUrl = $request->headers->get('referer', null);

        if (\is_string($refererUrl)) {
            $refererDomain = $this->getInnerSubstring($refererUrl, '/', 2);
            $currentDomain = $request->getHttpHost();

            if ($refererDomain !== $currentDomain) {
                foreach (self::EXCLUDE_URLS as $pattern) {
                    if (\preg_match($pattern, $refererUrl)) {
                        return;
                    }
                }

                $cookieId = $request->cookies->get(Referer::COOKIE_KEY, null);
                try {
                    $newCookieId = $this->refererService->addReferer($refererUrl, $request->getUri(), $cookieId);

                    if (!empty($newCookieId) && $newCookieId !== $cookieId) {
                        $this->newCookie = $newCookieId;
                    }
                } catch (\Exception $e) {
                    $this->logger->addError($e->getMessage());
                }
            }
        }
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
        $array = \explode($delim, $string);
        $pathArray = \is_array($array) ? $array : [];

        return isset($pathArray[$keyNumber]) ? $pathArray[$keyNumber] : '';
    }
}
