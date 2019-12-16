<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\User;
use App\Exception\BadAutoRegistrationDataException;
use App\Model\UserManager;
use App\Service\PaymentProcess\AbstractPaymentProcessService;
use App\Service\PaymentProcess\PaymentProcessInterface;
use App\Service\PaymentService;
use App\Service\Ticket\TicketService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * PaymentController.
 *
 * @Security("has_role('ROLE_USER')")
 */
class PaymentController extends Controller
{
    public const NEW_PAYMENT_SESSION_KEY = 'new_payment';

    /**
     * @Route("/event/{slug}/pay", name="event_pay")
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
                '@App/Page/index.html.twig',
                ['text' => $this->get('translator')->trans('error.payment.closed', ['%event%' => $event->getName()])]
            );
        }

        $payment = $this->get(PaymentService::class)->getPaymentForCurrentUser($event);

        $result = $this->get('serializer')->normalize($payment, null, ['groups' => ['payment.view']]);
        /** @var PaymentProcessInterface $paymentSystem */
        $paymentSystem = $this->get('app.payment_system.service');

        return $this->render(
            '@App/Redesign/Payment/payment.html.twig',
            [
                'event' => $event,
                'payment_data' => $result,
                'with_conditions' => $paymentSystem->isAgreeWithConditionsRequired(),
            ]
        );
    }

    /**
     * @Route("/event/{eventSlug}/payment/participant/edit/{id}", name="edit_ticket_participant",
     *     methods={"POST"},
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()")
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
        $paymentService = $this->get(PaymentService::class);
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

        $ticketService = $this->get(TicketService::class);
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
        $user->addWantsToVisitEvents($event);
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
     * @Route("/event/{slug}/payment/participant/add", name="add_ticket_participant",
     *     methods={"POST"},
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()")
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

        $paymentService = $this->get(PaymentService::class);
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
        $ticket = $em->getRepository(Ticket::class)
            ->findOneBy(['event' => $event->getId(), 'user' => $user->getId()]);

        if (!$ticket) {
            $ticket = $this->get(TicketService::class)->createTicket($event, $user);
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
        $paymentService = $this->get(PaymentService::class);
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
     * @Route("/event/{slug}/pay-by-bonus", name="event_pay_by_bonus")
     *
     * @param Event $event
     *
     * @return Response
     */
    public function setPaidByBonusMoneyAction(Event $event)
    {
        $result = false;
        $paymentService = $this->get(PaymentService::class);
        $payment = $paymentService->getPendingPaymentIfAccess($event);

        if ($payment && 0.0 === $payment->getAmount()) {
            $result = $this->get(PaymentService::class)->setPaidByBonusMoney($payment, $event);
        }

        if ($result) {
            $this->get('session')->set(AbstractPaymentProcessService::SESSION_PAYMENT_KEY, $payment->getId());

            return $this->redirect($this->generateUrl('payment_success'));
        }

        return $this->redirect($this->generateUrl('payment_fail'));
    }

    /**
     * Pay for payment by promocode (100% discount).
     *
     * @Route("/event/{slug}/pay-by-promocode", name="event_pay_by_promocode")
     *
     * @param Event $event
     *
     * @return Response
     */
    public function setPaidByPromocodeAction(Event $event)
    {
        $result = false;
        $paymentService = $this->get(PaymentService::class);
        $payment = $paymentService->getPendingPaymentIfAccess($event);

        if ($payment && 0.0 === $payment->getAmount()) {
            $result = $this->get(PaymentService::class)->setPaidByPromocode($payment, $event);
        }

        if ($result) {
            $this->get('session')->set(AbstractPaymentProcessService::SESSION_PAYMENT_KEY, $payment->getId());

            return $this->redirect($this->generateUrl('payment_success'));
        }

        return $this->redirect($this->generateUrl('payment_fail'));
    }

    /**
     * @Route("/payment-apply-fwdays-bonus/{slug}", name="payment_apply_fwdays_bonus",
     *     methods={"POST"},
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     *
     * @param Event   $event
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function applyFwdaysBonusAction(Event $event, Request $request): JsonResponse
    {
        $paymentService = $this->get(PaymentService::class);
        $payment = $paymentService->getPendingPaymentIfAccess($event);
        if (!$payment instanceof Payment) {
            return new JsonResponse(['result' => false, 'error' => 'Payment not found or access denied!']);
        }

        $amount = (float) $request->query->get('amount', 0);

        $this->get(PaymentService::class)->addFwdaysBonusToPayment($payment, $amount);
        $result = $this->get('serializer')->normalize($payment, null, ['groups' => ['payment.view']]);

        return new JsonResponse(['result' => true, 'payment_data' => $result]);
    }

    /**
     * @Route("/event/{slug}/paying", name="event_paying",
     *     methods={"POST"},
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     *
     * @param Event   $event
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function eventPayingAction(Event $event, Request $request): JsonResponse
    {
        $paymentService = $this->get(PaymentService::class);
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
                        $this->generateUrl('event_pay_by_bonus', ['slug' => $event->getSlug()]) : $this->generateUrl('event_pay_by_promocode', ['slug' => $event->getSlug()]);
                } else {
                    /** @var PaymentProcessInterface $paymentSystem */
                    $paymentSystem = $this->get('app.payment_system.service');
                    $formAction = $paymentSystem->getFormAction();
                    $paySystemData = $paymentSystem->getData($payment, $event);
                }
            }

            $form = $this->renderView('@App/Redesign/Payment/pay_form.html.twig', [
                'params' => $paySystemData,
                'action' => $formAction,
            ]);
        }

        return new JsonResponse(['result' => true, 'amount_changed' => $amountChanged, 'payment_data' => $paymentData, 'form' => $form]);
    }
}
