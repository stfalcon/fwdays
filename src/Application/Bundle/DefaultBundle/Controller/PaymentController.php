<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Entity\Payment;
use Application\Bundle\DefaultBundle\Entity\Ticket;
use Application\Bundle\DefaultBundle\Entity\User;
use Application\Bundle\DefaultBundle\Exception\BadAutoRegistrationDataException;
use Application\Bundle\DefaultBundle\Model\UserManager;
use Application\Bundle\DefaultBundle\Service\PaymentProcess\PaymentProcessInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * PaymentController.
 */
class PaymentController extends Controller
{
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
        $paymentService = $this->get('app.payment.service');
        $payment = $paymentService->getPendingPaymentIfAccess($event, $ticket);
        if (!$payment instanceof Payment) {
            return new JsonResponse(['result' => false, 'error' => ['user_name' => 'Payment not found or access denied!']]);
        }

        $name = $request->request->get('name');
        $surname = $request->request->get('surname');
        $email = $request->request->get('email');
        $promoCodeString = $request->request->get('promocode');

        $session = $this->get('session');
        $session->set(self::NEW_PAYMENT_SESSION_KEY, false);

        $userManager = $this->get('fos_user.user_manager');
        /** @var User|null $user */
        $user = $userManager->findUserBy(['email' => $email]);

        $ticketService = $this->get('app.ticket.service');
        if ($ticketService->isUserHasPaidTicketForEvent($user, $event)) {
            return new JsonResponse(
                [
                    'result' => false,
                    'error' => ['email' => $this->get('translator')->trans('error.user.already.paid', ['%email%' => $user->getEmail()])],
                ]
            );
        }

        try {
            $ticket = $paymentService->replaceIfFindOtherUserTicketForEvent($user, $event, $ticket);
        } catch (BadRequestHttpException $e) {
            return new JsonResponse(
                [
                    'result' => false,
                    'error' => ['email' => $this->get('translator')->trans('error.ticket.already.added')],
                ]
            );
        }

        $newUsersList = $session->get(UserManager::NEW_USERS_SESSION_KEY, []);
        if (!$user && \in_array($ticket->getUser()->getId(), $newUsersList, true)) {
            $user = $ticket->getUser();
        }

        try {
            if (!$user) {
                $user = $userManager->autoRegistration(['name' => $name, 'surname' => $surname, 'email' => $email]);
            } else {
                $userManager->updateUserData($user, $name, $surname, $email);
            }
        } catch (BadAutoRegistrationDataException $e) {
            return new JsonResponse(['result' => false, 'error' => $e->getErrorMap()]);
        } catch (BadRequestHttpException $e) {
            return new JsonResponse(['result' => false, 'error' => ['email' => $this->get('translator')->trans('error.user.cant_be_edit')]]);
        }

        $ticketService->setNewUserToTicket($user, $ticket);

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

        $paymentService = $this->get('app.payment.service');
        $payment = $paymentService->getPendingPaymentIfAccess($event);
        if (!$payment) {
            return new JsonResponse(['result' => false, 'error' => ['user_name' => 'Payment not found or access denied!']]);
        }

        $name = $request->request->get('name');
        $surname = $request->request->get('surname');
        $email = $request->request->get('email');
        $promoCodeString = $request->request->get('promocode');

        /** @var User|null $user */
        $user = $this->get('fos_user.user_manager')->findUserBy(['email' => $email]);
        if (!$user) {
            try {
                $user = $this->get('fos_user.user_manager')->autoRegistration(['name' => $name, 'surname' => $surname, 'email' => $email]);
            } catch (BadAutoRegistrationDataException $e) {
                $this->get('logger')->addError('autoRegistration with bad params');

                return new JsonResponse(['result' => false, 'error' => $e->getErrorMap()]);
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
                        'error' => ['email' => $this->get('translator')->trans('error.user.already.paid', ['%email%' => $user->getEmail()])],
                    ]
                );
            }

            if ($payment->getTickets()->contains($ticket)) {
                return new JsonResponse(
                    [
                        'result' => false,
                        'error' => ['email' => $this->get('translator')->trans('error.ticket.already.added')],
                    ]
                );
            }
        }

        $paymentService->addTicketToPayment($payment, $ticket);
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
        $paymentService = $this->get('app.payment.service');
        $payment = $paymentService->getPendingPaymentIfAccess($event, $ticket);
        if (!$payment) {
            return new JsonResponse(['result' => false, 'error' => 'Payment not found or access denied!', 'html' => '']);
        }

        if ($payment->getTickets()->count() > 1) {
            $paymentService->removeTicketFromPayment($payment, $ticket);
            $paymentService->checkTicketsPricesInPayment($payment, $event);
        } else {
            return new JsonResponse(['result' => false, 'error' => 'Must be at least one ticket!', 'html' => '']);
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
        $paymentService = $this->get('app.payment.service');
        $payment = $paymentService->getPendingPaymentIfAccess($event);

        if ($payment && 0.0 === $payment->getAmount()) {
            $result = $this->get('app.payment.service')->setPaidByBonusMoney($payment, $event);
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
        $paymentService = $this->get('app.payment.service');
        $payment = $paymentService->getPendingPaymentIfAccess($event);

        if ($payment && 0.0 === $payment->getAmount()) {
            $result = $this->get('app.payment.service')->setPaidByPromocode($payment, $event);
        }

        $redirectUrl = $result ? $this->generateUrl('payment_success') : $this->generateUrl('payment_fail');

        return $this->redirect($redirectUrl);
    }

    /**
     * @Route("/payment-apply-fwdays-bonus/{slug}", name="payment_apply_fwdays_bonus",
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
    public function applyFwdaysBonusAction(Event $event, Request $request): JsonResponse
    {
        $paymentService = $this->get('app.payment.service');
        $payment = $paymentService->getPendingPaymentIfAccess($event);
        if (!$payment instanceof Payment) {
            return new JsonResponse(['result' => false, 'error' => 'Payment not found or access denied!']);
        }

        $amount = (float) $request->query->get('amount', 0);

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
        $paymentService = $this->get('app.payment.service');
        $payment = $paymentService->getPendingPaymentIfAccess($event);
        if (!$payment instanceof Payment) {
            return new JsonResponse(['result' => false, 'error' => 'Payment not found or access denied!']);
        }

        $paymentService->checkTicketsPricesInPayment($payment, $event);

        $savedData = (float) $request->request->get('saved_data');
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
                if (0.0 === $payment->getAmount()) {
                    $formAction = $payment->getFwdaysAmount() > 0 ?
                        $this->generateUrl('event_pay_by_bonus', ['eventSlug' => $event->getSlug()]) : $this->generateUrl('event_pay_by_promocode', ['eventSlug' => $event->getSlug()]);
                } else {
                    /** @var PaymentProcessInterface $paymentSystem */
                    $paymentSystem = $this->get('app.payment_system.service');
                    $formAction = $paymentSystem->getFormAction();
                    $paySystemData = $paymentSystem->getData($payment, $event);
                }
            }

            $form = $this->renderView('@ApplicationDefault/Redesign/Payment/pay_form.html.twig', [
                'params' => $paySystemData,
                'action' => $formAction,
            ]);
        }

        return new JsonResponse(['result' => true, 'amount_changed' => $amountChanged, 'payment_data' => $paymentData, 'form' => $form]);
    }
}
