<?php

namespace App\EventListener;

use App\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * PromoCodeRequestListener.
 */
class PromoCodeRequestListener
{
    private const PROMO_CODE_QUERY_KEY = 'promocode';

    private $router;
    private $session;

    /**
     * @param Router  $router
     * @param Session $session
     */
    public function __construct(Router $router, Session $session)
    {
        $this->router = $router;
        $this->session = $session;
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
        $promocode = $request->query->get(self::PROMO_CODE_QUERY_KEY);
        if ($promocode && \preg_match('/\/event\/.+/', $request->getPathInfo())) {
            $eventSlugStartPos = \strpos($request->getPathInfo(), '/event/') + \strlen('/event/');
            $eventSlugEndPos = \strpos($request->getPathInfo(), '/', $eventSlugStartPos);
            $eventSlugLength = false !== $eventSlugEndPos ? $eventSlugEndPos - $eventSlugStartPos : null;
            if (null !== $eventSlugLength) {
                $eventSlug = \substr($request->getPathInfo(), $eventSlugStartPos, $eventSlugLength);
            } else {
                $eventSlug = \substr($request->getPathInfo(), $eventSlugStartPos);
            }
            $currentPromoCodes = $this->session->get(PaymentService::PROMO_CODE_SESSION_KEY, []);
            $currentPromoCodes[$eventSlug] = $promocode;
            $this->session->set(PaymentService::PROMO_CODE_SESSION_KEY, $currentPromoCodes);

            $url = $this->router->generate('event_show', ['slug' => $eventSlug]);
            $event->setResponse(new RedirectResponse($url));
        }
    }
}
