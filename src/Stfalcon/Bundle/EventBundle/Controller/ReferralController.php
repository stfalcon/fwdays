<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Stfalcon\Bundle\EventBundle\Service\ReferralService;

/**
 * Referral controller
 */
class ReferralController extends BaseController
{
    /**
     * @param string $code  Code
     * @param string $eventSlug Event
     *
     * @Route("/ref/{code}/event/{eventSlug}", name="referral_link")
     *
     * @return RedirectResponse
     */
    public function referralAction($code, $eventSlug)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $referralService = $this->get('stfalcon_event.referral.service');

        if ($referralService->getReferralCode($user) !== $code) {
            $response = new Response();
            $expire = time() + (10 * 365 * 24 * 3600);

            $response->headers->setCookie(new Cookie(ReferralService::REFERRAL_CODE, $code, $expire));
            $response->send();
        }

        $em = $this->getDoctrine()->getEntityManager();
        $event = $em->getRepository('StfalconEventBundle:Event')->findBy(['slug' => $eventSlug]);

        if ($event) {
            $url = $this->generateUrl('event_show', ['event_slug' => $eventSlug]);
        } else {
            $url = $this->generateUrl('homepage');
        }

        return $this->redirect($url);
    }

    /**
     *
     * @return array
     *
     * @Secure(roles="ROLE_USER")
     *
     * @Route("/referral", name="referral_page")
     *
     * @Template()
     */
    public function indexAction()
    {
        $referralService = $this->get('stfalcon_event.referral.service');

        /**
         * @var User $user
         */
        $user = $this->getUser();

        return [
            'balance' => $user->getBalance(),
            'code'    => $referralService->getReferralCode()
        ];
    }
}
