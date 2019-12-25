<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Service\ReferralService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Referral.
 */
class ReferralController extends AbstractController
{
    private $referralService;

    /**
     * @param ReferralService $referralService
     */
    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

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

        if ($this->referralService->getReferralCode($user) !== $code) {
            $response = new Response();
            $expire = time() + (10 * 365 * 24 * 3600);

            $response->headers->setCookie(new Cookie(ReferralService::REFERRAL_CODE, $code, $expire));
            $response->send();
        }

        $url = $this->generateUrl('event_show', ['slug' => $event->getSlug()]);

        return $this->redirect($url);
    }
}
