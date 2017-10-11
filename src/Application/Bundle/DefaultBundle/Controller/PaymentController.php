<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Stfalcon\Bundle\EventBundle\Entity\PromoCode;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class PaymentController.
 */
class PaymentController extends Controller
{
    /**
     * Event pay.
     *
     * @Route("/event/{eventSlug}/pay", name="event_pay",
     *     methods={"POST"},
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     * @Security("has_role('ROLE_USER')")
     *
     * @ParamConverter("event", options={"mapping": {"eventSlug": "slug"}})
     *
     * @param Event $event
     *
     * @return JsonResponse
     */
    public function payAction(Event $event)
    {
        $html = '';
        if (!$event->getReceivePayments()) {
            return new JsonResponse(['result' => false, 'error' => "Оплата за участие в {$event->getName()} не принимается.", 'html' => $html]);
        }
        /* @var  User $user */
        $user = $this->getUser();

        /* @var $ticket Ticket */
        $ticket = $this->getDoctrine()->getManager()
            ->getRepository('StfalconEventBundle:Ticket')
            ->findOneBy(['user' => $user->getId(), 'event' => $event->getId()]);

        $paymentService = $this->get('stfalcon_event.payment.service');

        /** @var Payment $payment */
        $payment = $this->getDoctrine()->getManager()->getRepository('StfalconEventBundle:Payment')
            ->findPaymentByUserAndEvent($user, $event);

        if (!$ticket && !$payment) {
            $ticket = $this->get('stfalcon_event.ticket.service')->createTicket($event, $user);
        }

        if ($ticket && !$payment = $ticket->getPayment()) {
            $payment = $paymentService->createPaymentForCurrentUserWithTicket($ticket);
        }

        if (!$payment) {
            return new JsonResponse(['result' => false, 'error' => 'Payment not found!', 'html' => $html]);
        }

        if ($payment->isPaid()) {
            return new JsonResponse(['result' => false, 'error' => 'Payment is paid', 'html' => $html]);
        }

        if ($payment->isPending()) {
            $paymentService->checkTicketsPricesInPayment($payment, $event);
            $paymentService->payByReferralMoney($payment);
        }

        $this->get('session')->set('active_payment_id', $payment->getId());

        return $this->getPaymentHtml($event, $payment);
    }

    /**
     * @Route(path="/addPromoCode/{code}/{eventSlug}", name="add_promo_code",
     *     methods={"POST"},
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     * @ParamConverter("event", options={"mapping": {"eventSlug": "slug"}})
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @param string $code
     * @param Event  $event
     *
     * @return JsonResponse
     */
    public function addPromoCodeAction($code, Event $event)
    {
        $payment = $this->getPaymentFromSession();

        if (!$payment) {
            return new JsonResponse(['result' => false, 'error' => 'Payment not found!', 'html' => '']);
        }

        $em = $this->getDoctrine()->getManager();
        $promoCode = $em->getRepository('StfalconEventBundle:PromoCode')
            ->findActivePromoCodeByCodeAndEvent($code, $event);

        if (!$promoCode) {
            return new JsonResponse(['result' => false, 'error' => 'Promo-code not found!', 'html' => '']);
        }

        return $this->getPaymentHtml($event, $payment, $promoCode);
    }

    /**
     * Add user to payment.
     *
     * @Route("/event/{eventSlug}/payment/participant/add/{name}/{surname}/{email}", name="add_participant_to_payment",
     *     methods={"POST"},
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @ParamConverter("event", options={"mapping": {"eventSlug": "slug"}})
     *
     * @param Event  $event
     * @param string $name
     * @param string $surname
     * @param string $email
     *
     * @return JsonResponse
     */
    public function addParticipantToPaymentAction(Event $event, $name, $surname, $email)
    {
        $payment = $this->getPaymentFromSession();

        if (!$payment) {
            return new JsonResponse(['result' => false, 'error' => 'Payment not found!', 'html' => '']);
        }

        if ($payment->isPaid()) {
            return new JsonResponse(['result' => false, 'error' => 'Can not allow paid!', 'html' => '']);
        }
        /** @var User $user */
        $user = $this->get('fos_user.user_manager')->findUserBy(['email' => $email]);

        if (!$user) {
            $user = $this->get('fos_user.user_manager')->autoRegistration(['name' => $name, 'surname' => $surname, 'email' => $email]);
        }

        $em = $this->getDoctrine()->getManager();

        /** @var Ticket $ticket */
        $ticket = $em->getRepository('StfalconEventBundle:Ticket')
            ->findOneBy(['event' => $event->getId(), 'user' => $user->getId()]);

        if (!$ticket) {
            $ticketService = $this->get('stfalcon_event.ticket.service');
            $ticket = $ticketService->createTicket($event, $user);
        }

        if (!$ticket->isPaid()) {
            $paymentService = $this->get('stfalcon_event.payment.service');
            $paymentService->addTicketToPayment($payment, $ticket);
            $paymentService->checkTicketsPricesInPayment($payment, $event);
        } else {
            return new JsonResponse(['result' => false, 'error' => 'User'.$user->getName().$user->getSurName().' already paid ticket!', 'html' => '']);
        }

        return $this->getPaymentHtml($event, $payment);
    }

    /**
     * Remove user/ticket from payment.
     *
     * @Route("/event/{eventSlug}/ticket/{id}/remove", name="remove_ticket_from_payment",
     *     methods={"POST"},
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     * @Security("has_role('ROLE_USER')")
     *
     * @ParamConverter("event", options={"mapping": {"eventSlug": "slug"}})
     *
     * @param Event  $event
     * @param Ticket $ticket
     *
     * @return JsonResponse
     */
    public function removeTicketFromPaymentAction(Event $event, Ticket $ticket)
    {
        $payment = $this->getPaymentFromSession();

        if (!$payment) {
            return new JsonResponse(['result' => false, 'error' => 'Payment not found!', 'html' => '']);
        }

        if (!$ticket->isPaid() && $payment->getTickets()->count() > 1) {
            $paymentService = $this->get('stfalcon_event.payment.service');
            $paymentService->removeTicketFromPayment($payment, $ticket);
            $paymentService->checkTicketsPricesInPayment($payment, $event);
        } else {
            return new JsonResponse(['result' => false, 'error' => 'Already paid ticket!', 'html' => '']);
        }

        return $this->getPaymentHtml($event, $payment);
    }

    /**
     * Get payment html for popup.
     *
     * @param Event     $event
     * @param Payment   $payment
     * @param Promocode $promoCode
     *
     * @return JsonResponse
     */
    private function getPaymentHtml(Event $event, Payment $payment, PromoCode $promoCode = null)
    {
        $ikData = $this->get('stfalcon_event.interkassa.service')->getData($payment, $event);
        $paymentsConfig = $this->container->getParameter('stfalcon_event.config');
        $discountAmount = 100 * (float) $paymentsConfig['discount'];

        $notUsedPromoCode = [];
        $paymentService = $this->get('stfalcon_event.payment.service');

        if (!$promoCode) {
            $promoCode = $paymentService->getPromoCodeFromPaymentTickets($payment);
        }
        if ($promoCode) {
            $notUsedPromoCode = $paymentService->addPromoCodeForTicketsInPayment($payment, $promoCode);
        }

        $html = $this->renderView('@ApplicationDefault/Redesign/pay.html.twig', [
            'data' => $ikData,
            'event' => $event,
            'payment' => $payment,
            'discountAmount' => $discountAmount,
        ]);

        $paymentSums = $this->renderView('@ApplicationDefault/Redesign/payment.sums.html.twig', ['payment' => $payment]);
        /**
         * @var User
         */
        $user = $this->getUser();

        return new JsonResponse([
            'result' => true,
            'error' => '',
            'html' => $html,
            'paymentSums' => $paymentSums,
            'notUsedPromoCode' => $notUsedPromoCode,
            'phoneNumber' => $user->getPhone(),
        ]);
    }

    /**
     * Check if payment correct and give it by id.
     *
     * @return Payment $payment
     */
    private function getPaymentFromSession()
    {
        $em = $this->getDoctrine()->getManager();
        $payment = null;
        if ($this->get('session')->has('active_payment_id')) {
            $paymentId = $this->get('session')->get('active_payment_id');
            $payment = $em->getRepository('StfalconEventBundle:Payment')->find($paymentId);
        }
        $payment = $payment && $payment->getUser() === $this->getUser() ? $payment : null;

        return $payment;
    }
}
