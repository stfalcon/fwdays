<?php

namespace Application\Bundle\DefaultBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Class PromoCodeRequestListener.
 */
class PromoCodeRequestListener
{
    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $promocode = $request->query->get('promocode');
        if ($promocode && preg_match('/\/event\/.+/', $request->getPathInfo())) {
            $eventSlugStartPos = strpos($request->getPathInfo(), '/event/') + strlen('/event/');
            $eventSlugEndPos = strpos($request->getPathInfo(), '/', $eventSlugStartPos);
            $eventSlugLength = false !== $eventSlugEndPos ? $eventSlugEndPos - $eventSlugStartPos : null;
            if (null !== $eventSlugLength) {
                $eventSlug = substr($request->getPathInfo(), $eventSlugStartPos, $eventSlugLength);
            } else {
                $eventSlug = substr($request->getPathInfo(), $eventSlugStartPos);
            }
            $url = $request->getBaseUrl().$request->getPathInfo();
            $response = new RedirectResponse($url);
            $cookie = new Cookie('promocode', $promocode, time() + 3600, '/', null, false, false);
            $response->headers->setCookie($cookie);
            $cookie = new Cookie('promoevent', $eventSlug, time() + 3600, '/', null, false, false);
            $response->headers->setCookie($cookie);
            $event->setResponse($response);
        }
    }
}
