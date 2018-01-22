<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Application\Bundle\DefaultBundle\Service\ReferralService;

/**
 * Referral controller.
 */
class ReferralController extends Controller
{
    /**
     * @param string $code      Code
     * @param string $eventSlug Event
     *
     * @Route("/ref/{code}/event/{eventSlug}", name="referral_link")
     *
     * @return RedirectResponse
     */
    public function referralAction($code, $eventSlug)
    {
        /**
         * @var User
         */
        $user = $this->getUser();

        $referralService = $this->get('stfalcon_event.referral.service');

        if ($referralService->getReferralCode($user) !== $code) {
            $response = new Response();
            $expire = time() + (10 * 365 * 24 * 3600);

            $response->headers->setCookie(new Cookie(ReferralService::REFERRAL_CODE, $code, $expire));
            $response->send();
        }

        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('StfalconEventBundle:Event')->findBy(['slug' => $eventSlug]);

        if ($event) {
            $url = $this->generateUrl('event_show', ['eventSlug' => $eventSlug]);
        } else {
            $url = $this->generateUrl('homepage');
        }

        return $this->redirect($url);
    }
}
