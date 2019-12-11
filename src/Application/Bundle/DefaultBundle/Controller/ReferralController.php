<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Entity\User;
use Application\Bundle\DefaultBundle\Service\ReferralService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Referral.
 */
class ReferralController extends Controller
{
    /**
     * @Route("/ref/{code}/event/{slug}", name="referral_link")
     *
     * @param string $code
     * @param Event  $event
     *
     * @return RedirectResponse
     */
    public function referralAction(string $code, Event $event): RedirectResponse
    {
        /** @var User */
        $user = $this->getUser();

        $referralService = $this->get('app.referral.service');

        if ($referralService->getReferralCode($user) !== $code) {
            $response = new Response();
            $expire = time() + (10 * 365 * 24 * 3600);

            $response->headers->setCookie(new Cookie(ReferralService::REFERRAL_CODE, $code, $expire));
            $response->send();
        }

        $url = $this->generateUrl('event_show', ['slug' => $event->getSlug()]);

        return $this->redirect($url);
    }
}
