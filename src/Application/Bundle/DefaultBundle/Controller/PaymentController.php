<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\User;
use Application\Bundle\DefaultBundle\Service\WayForPayService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Application\Bundle\DefaultBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Application\Bundle\DefaultBundle\Entity\PromoCode;
use Application\Bundle\DefaultBundle\Entity\Payment;
use Application\Bundle\DefaultBundle\Entity\Ticket;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * Class PaymentController.
 */
class PaymentController extends Controller
{
    private const NEW_USERS_SESSION_KEY = 'new_users';
    public const ACTIVE_PAYMENT_ID_KEY = 'active_payment_id';
    public const NEW_PAYMENT_SESSION_KEY = 'new_payment';

    /**
     * @Route("/event/{slug}/pay", name="event_pay")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @param Event $event
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function payAction(Event $event): Response
    {
        if (!$event->getReceivePayments() || !$event->isHaveFreeTickets()) {
            return $this->render(
                '@ApplicationDefault/Page/index.html.twig',
                ['text' => $this->get('translator')->trans('error.payment.closed', ['%event%' => $event->getName()])]
            );
        }

        $payment = $this->get('app.payment.service')->getPaymentForCurrentUser($event);

        $result = $this->get('serializer')->normalize($payment, null, ['groups' => ['payment.view']]);

        return $this->render('@ApplicationDefault/Redesign/Payment/payment.html.twig', ['event' => $event, 'payment_data' => $result]);
    }

    /**
     * @Route("/event/{eventSlug}/payment/participant/edit/{id}", name="edit_ticket_participant",
     *     methods={"POST"},
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @ParamConverter("event", options={"mapping": {"eventSlug": "slug"}})
     *
     * @param Event   $event
     * @param Ticket  $ticket
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function editTicketParticipantAction(Event $event, Ticket $ticket, Request $request): JsonResponse
    {
        if (!$event->getReceivePayments() || !$event->isHaveFreeTickets()) {
            return new JsonResponse(
                [
                    'result' => false,
                    'error' => ['user_name' => $this->get('translator')->trans('error.payment.closed', ['%event%' => $event->getName()])],
                ]
            );
        }
        $name = $request->request->get('name');
        $surname = $request->request->get('surname');
        $email = $request->request->get('email');
        $promoCodeString = $request->request->get('promocode');

        $payment = $this->getPaymentIfAccess($ticket);
        if (!$payment) {
            return new JsonResponse(['result' => false, 'error' => ['user_name' => 'Payment not found or access denied!']]);
        }

        if ($payment->isPaid()) {
            return new JsonResponse(['result' => false, 'error' => ['user_name' => 'Payment paid!']]);
        }

        $session = $this->get('session');
        $session->set(self::NEW_PAYMENT_SESSION_KEY, false);

        /** @var User|null $user */
        $user = $this->get('fos_user.user_manager')->findUserBy(['email' => $email]);
        $userManager = $this->get('fos_user.user_manager');
        $newUsersList = $session->get(self::NEW_USERS_SESSION_KEY, []);

        if (!$user && $ticket->getUser() instanceof User && \in_array($ticket->getUser()->getId(), $newUsersList, true)) {
            $user = $ticket->getUser();
        }

        if (!$user) {
            try {
                $user = $userManager->autoRegistration(['name' => $name, 'surname' => $surname, 'email' => $email]);
                $newUsersList[] = $user->getId();
                $session->set(self::NEW_USERS_SESSION_KEY, $newUsersList);
            } catch (BadCredentialsException $e) {
                $this->get('logger')->addError('autoRegistration with bad params');

                return new JsonResponse(['result' => false, 'error' => ['user_email' => 'Bad credentials!']]);
            }
        } else {
            if (\in_array($user->getId(), $newUsersList, true)) {
                try {
                    $userManager->updateUserData($user, $name, $surname, $email);
                } catch (BadCredentialsException $e) {
                    $this->get('logger')->addError('autoRegistration with bad params');

                    return new JsonResponse(['result' => false, 'error' => ['user_email' => 'Bad credentials!']]);
                }
            }
        }

        $oldUser = $ticket->getUser();
        if ($user instanceof User && !$user->isEqualTo($oldUser)) {
            $oldUser->removeTicket($ticket);
            $user->addTicket($ticket);
        }

        $paymentService = $this->get('app.payment.service');
        try {
            $paymentService->addPromoCodeForTicketByCode($promoCodeString, $event, $ticket);
        } catch (BadRequestHttpException $e) {
            return new JsonResponse(['result' => false, 'error' => ['user_promo_code' => $e->getMessage()]]);
        }

        $paymentService->checkTicketsPricesInPayment($payment, $event);

        $paymentData = $this->get('serializer')->normalize($payment, null, ['groups' => ['payment.view']]);
        $ticketData = $this->get('serializer')->normalize($ticket, null, ['groups' => ['payment.view']]);

        return new JsonResponse(['result' => true, 'payment_data' => $paymentData, 'ticket_data' => $ticketData]);
    }

    /**
     * Add user to payment.
     *
     * @Route("/event/{eventSlug}/payment/participant/add", name="add_ticket_participant",
     *     methods={"POST"},
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @ParamConverter("event", options={"mapping": {"eventSlug": "slug"}})
     *
     * @param Event   $event
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addTicketParticipantAction(Event $event, Request $request): JsonResponse
    {
        if (!$event->getReceivePayments() || !$event->isHaveFreeTickets()) {
            return new JsonResponse(
                [
                    'result' => false,
                    'error' => $this->get('translator')->trans('error.payment.closed', ['%event%' => $event->getName()]),
                    'path' => 'user_name',
                ]
            );
        }

        $payment = $this->getPaymentIfAccess();

        if (!$payment) {
            return new JsonResponse(['result' => false, 'error' => ['user_name' => 'Payment not found or access denied!']]);
        }

        if ($payment->isPaid()) {
            return new JsonResponse(['result' => false, 'error' => ['user_name' => 'Payment paid!']]);
        }

        $name = $request->request->get('name');
        $surname = $request->request->get('surname');
        $email = $request->request->get('email');
        $promoCodeString = $request->request->get('promocode');

        /** @var User|null $user */
        $user = $this->get('fos_user.user_manager')->findUserBy(['email' => $email]);
        $session = $this->get('session');
        if (!$user) {
            try {
                $user = $this->get('fos_user.user_manager')->autoRegistration(['name' => $name, 'surname' => $surname, 'email' => $email]);
                $newUsersList = $session->get(self::NEW_USERS_SESSION_KEY, []);
                $newUsersList[] = $user->getId();
                $session->set(self::NEW_USERS_SESSION_KEY, $newUsersList);
            } catch (BadCredentialsException $e) {
                $this->get('logger')->addError('autoRegistration with bad params');

                return new JsonResponse(['result' => false, 'error' => ['user_name' => 'Bad credentials!']]);
            }
        }

        $em = $this->getDoctrine()->getManager();

        /** @var Ticket|null $ticket */
        $ticket = $em->getRepository('ApplicationDefaultBundle:Ticket')
            ->findOneBy(['event' => $event->getId(), 'user' => $user->getId()]);

        if (!$ticket) {
            $ticket = $this->get('app.ticket.service')->createTicket($event, $user);
            $user->addWantsToVisitEvents($event);
            $em->flush();
        } else {
            if ($ticket->isPaid()) {
                return new JsonResponse(
                    [
                        'result' => false,
                        'error' => ['user_name' => $this->get('translator')->trans('error.user.already.paid', ['%email%' => $user->getEmail()])],
                    ]
                );
            }

            if ($payment->getTickets()->contains($ticket)) {
                return new JsonResponse(
                    [
                        'result' => false,
                        'error' => ['user_email' => $this->get('translator')->trans('error.ticket.already.added')],
                    ]
                );
            }
        }

        $paymentService = $this->get('app.payment.service');
        try {
            $paymentService->addPromoCodeForTicketByCode($promoCodeString, $event, $ticket);
        } catch (BadRequestHttpException $e) {
            return new JsonResponse(['result' => false, 'error' => ['user_promo_code' => $e->getMessage()]]);
        }

        $paymentService->addTicketToPayment($payment, $ticket);
        $paymentService->checkTicketsPricesInPayment($payment, $event);

        $paymentData = $this->get('serializer')->normalize($payment, null, ['groups' => ['payment.view']]);

        return new JsonResponse(['result' => true, 'payment_data' => $paymentData]);
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
        $payment = $this->getPaymentIfAccess($ticket);

        if (!$payment) {
            return new JsonResponse(['result' => false, 'error' => 'Payment not found or access denied!', 'html' => '']);
        }

        if (!$ticket->isPaid() && $payment->getTickets()->count() > 1) {
            $paymentService = $this->get('app.payment.service');
            $paymentService->removeTicketFromPayment($payment, $ticket);
            $paymentService->checkTicketsPricesInPayment($payment, $event);
        } else {
            return new JsonResponse(['result' => false, 'error' => 'Already paid ticket!', 'html' => '']);
        }

        $result = $this->get('serializer')->normalize($payment, null, ['groups' => ['payment.view']]);

        return new JsonResponse(['result' => true, 'payment_data' => $result]);
    }

    /**
     * Pay for payment by bonus money.
     *
     * @Route("/event/{eventSlug}/pay-by-bonus", name="event_pay_by_bonus")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @ParamConverter("event", options={"mapping": {"eventSlug": "slug"}})
     *
     * @param Event $event
     *
     * @return Response
     */
    public function setPaidByBonusMoneyAction(Event $event)
    {
        $result = false;
        $payment = $this->getPaymentIfAccess();

        if ($payment && $payment->isPending() && 0 === (int) $payment->getAmount()) {
            $paymentService = $this->get('app.payment.service');
            $result = $paymentService->setPaidByBonusMoney($payment, $event);
        }

        $redirectUrl = $result ? $this->generateUrl('payment_success') : $this->generateUrl('payment_fail');

        return $this->redirect($redirectUrl);
    }

    /**
     * Pay for payment by promocode (100% discount).
     *
     * @Route("/event/{eventSlug}/pay-by-promocode", name="event_pay_by_promocode")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @ParamConverter("event", options={"mapping": {"eventSlug": "slug"}})
     *
     * @param Event $event
     *
     * @return Response
     */
    public function setPaidByPromocodeAction(Event $event)
    {
        $result = false;
        $payment = $this->getPaymentIfAccess();

        if ($payment && $payment->isPending() && 0 === (int) $payment->getAmount()) {
            $paymentService = $this->get('app.payment.service');
            $result = $paymentService->setPaidByPromocode($payment, $event);
        }

        $redirectUrl = $result ? $this->generateUrl('payment_success') : $this->generateUrl('payment_fail');

        return $this->redirect($redirectUrl);
    }

    /**
     * @Route("/payment-apply-fwdays-bonus", name="payment_apply_fwdays_bonus",
     *     methods={"POST"},
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function applyFwdaysBonusAction(Request $request): JsonResponse
    {
        $payment = $this->getPaymentIfAccess();
        if (!$payment instanceof Payment) {
            return new JsonResponse(['result' => false, 'error' => 'Payment not found or access denied!']);
        }

        $amount = $request->query->getInt('amount');

        $this->get('app.payment.service')->addFwdaysBonusToPayment($payment, $amount);
        $result = $this->get('serializer')->normalize($payment, null, ['groups' => ['payment.view']]);

        return new JsonResponse(['result' => true, 'payment_data' => $result]);
    }

    /**
     * @Route("/event/{slug}/paying", name="event_paying",
     *     methods={"POST"},
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @param Event   $event
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function eventPayingAction(Event $event, Request $request): JsonResponse
    {
        $payment = $this->getPaymentIfAccess();
        if (!$payment instanceof Payment) {
            return new JsonResponse(['result' => false, 'error' => 'Payment not found or access denied!']);
        }

        if ($payment->isPaid()) {
            return new JsonResponse(['result' => false, 'error' => 'Payment paid!']);
        }

        $paymentService = $this->get('app.payment.service');
        $paymentService->checkTicketsPricesInPayment($payment, $event);

        $savedData = $request->request->get('saved_data');
        $paymentData = $this->get('serializer')->normalize($payment, null, ['groups' => ['payment.view']]);

        $form = null;
        $amountChanged = $savedData !== $paymentData['amount'];
        if ($amountChanged) {
            $paymentData['amount_changed_text'] = $this->get('translator')->trans('pay.amount_changed');
        } else {
            $formAction = null;
            $payType = null;
            $paySystemData = null;
            if ($payment->getTickets()->count() > 0) {
                if (0 === (int) $payment->getAmount()) {
                    $formAction = $payment->getFwdaysAmount() > 0 ?
                        $this->generateUrl('event_pay_by_bonus', ['eventSlug' => $event->getSlug()]) : $this->generateUrl('event_pay_by_promocode', ['eventSlug' => $event->getSlug()]);
                } else {
                    $formAction = WayForPayService::WFP_SECURE_PAGE;
                    $this->get('session')->set(WayForPayService::WFP_PAYMENT_KEY, $payment->getId());
                    $paySystemData = $this->get('app.way_for_pay.service')->getData($payment, $event);
                }
            }

            $form = $this->renderView('@ApplicationDefault/Redesign/Payment/pay_form.html.twig', [
                'params' => $paySystemData,
                'action' => $formAction,
            ]);
        }

        return new JsonResponse(['result' => true, 'amount_changed' => $amountChanged, 'payment_data' => $paymentData, 'form' => $form]);
    }

    /**
     * Check if payment correct and give it by id.
     *
     * @param Ticket $ticket
     *
     * @return Payment|null $payment
     */
    private function getPaymentIfAccess($ticket = null): ?Payment
    {
        $payment = null;
        $session = $this->get('session');
        if ($session->has(self::ACTIVE_PAYMENT_ID_KEY)) {
            $paymentId = $session->get(self::ACTIVE_PAYMENT_ID_KEY);
            $em = $this->getDoctrine()->getManager();
            $payment = $em->getRepository('ApplicationDefaultBundle:Payment')->find($paymentId);
        }

        if (!$payment instanceof Payment) {
            return null;
        }

        $paymentUser = $payment->getUser();
        $currentUser = $this->getUser();

        if ($ticket instanceof Ticket) {
            $ticketUser = $ticket->getUser();
            $payment = $payment->getTickets()->contains($ticket) && ($ticketUser->isEqualTo($currentUser) || $paymentUser->isEqualTo($currentUser)) ? $payment : null;
        } else {
            $payment = $paymentUser->isEqualTo($currentUser) ? $payment : null;
        }

        return $payment;
    }
}
