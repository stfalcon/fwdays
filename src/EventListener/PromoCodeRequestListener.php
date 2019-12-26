<?php

namespace App\EventListener;

use App\Service\PaymentService;
use App\Traits;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * PromoCodeRequestListener.
 */
class PromoCodeRequestListener implements EventSubscriberInterface
{
    use Traits\SessionTrait;
    use Traits\RouterTrait;

    private const PROMO_CODE_QUERY_KEY = 'promocode';

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): \Generator
    {
        yield KernelEvents::REQUEST => 'onKernelRequest';
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
