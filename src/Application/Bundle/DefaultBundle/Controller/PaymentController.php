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
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * Class PaymentController.
 */
class PaymentController extends Controller
{
    private const ACTIVE_PAYMENT_ID_KEY = 'active_payment_id';
    private const NEW_USERS_SESSION_KEY = 'new_users';

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
        /* @var  User $user */
        $user = $this->getUser();

        /* @var Ticket|null $ticket  */
        $ticket = $this->getDoctrine()->getManager()
            ->getRepository('ApplicationDefaultBundle:Ticket')
            ->findOneBy(['user' => $user->getId(), 'event' => $event->getId()]);

        $paymentService = $this->get('app.payment.service');

        /** @var Payment|null $payment */
        $payment = $this->getDoctrine()->getManager()->getRepository('ApplicationDefaultBundle:Payment')
            ->findPaymentByUserAndEvent($user, $event);
        $em = $this->getDoctrine()->getManager();
        if (!$ticket && !$payment) {
            $ticket = $this->get('app.ticket.service')->createTicket($event, $user);
            $user->addWantsToVisitEvents($event);
            $em->flush();
        }

        if (!$payment && $ticket->getPayment() && !$ticket->getPayment()->isReturned()) {
            $payment = $ticket->getPayment();
            $payment->setUser($ticket->getUser());
            $em->flush();
        }

        if ($ticket && !$payment) {
            $payment = $paymentService->createPaymentForCurrentUserWithTicket($ticket);
        } elseif ($ticket && $payment->isPaid()) {
            $payment = $paymentService->createPaymentForCurrentUserWithTicket(null);
        }

        if ($payment->isPending()) {
            $paymentService->checkTicketsPricesInPayment($payment, $event);
        }

        $this->get('session')->set(self::ACTIVE_PAYMENT_ID_KEY, $payment->getId());

//        $promoCode = null;
//        $request = $this->get('request_stack')->getCurrentRequest();
//        if ($request && $code = $request->query->get('promoCode')) {
//            $promoCode = $this->getPromoCodeFromQuery($event, $payment, $code);
//        }

        $result = $this->get('serializer')->normalize($payment, null, ['groups' => ['payment.view']]);

        return $this->render('@ApplicationDefault/Redesign/Payment/payment.html.twig', ['event' => $event, 'payment_data' => $result]);
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
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function addPromoCodeAction($code, Event $event)
    {
        $payment = $this->getPaymentIfAccess();
        $translator = $this->get('translator');
        if (!$payment) {
            return new JsonResponse(['result' => false, 'error' => 'Payment not found or access denied!', 'html' => '']);
        }

        if ($payment->isPaid()) {
            return new JsonResponse(['result' => false, 'error' => 'Payment paid!', 'html' => '']);
        }

        $em = $this->getDoctrine()->getManager();
        /** @var PromoCode|null $promoCode */
        $promoCode = $em->getRepository('ApplicationDefaultBundle:PromoCode')
            ->findActivePromoCodeByCodeAndEvent($code, $event);

        if (!$promoCode) {
            return new JsonResponse(['result' => false, 'error' => $translator->trans('error.promocode.not_found'), 'html' => '']);
        }

        if (!$promoCode->isCanBeUsed()) {
            return new JsonResponse(['result' => false, 'error' => $translator->trans('error.promocode.used'), 'html' => '']);
        }

        if ($payment->isPending()) {
            $paymentService = $this->get('app.payment.service');
            $paymentService->checkTicketsPricesInPayment($payment, $event);
        }

        return $this->getPaymentHtml($event, $payment, $promoCode);
    }

    /**
     * static payment.
     *
     * @Route(path="/static-payment/{slug}")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @param Event $event
     *
     * @return Response
     */
    public function staticPaymentAction(Event $event): Response
    {
        if (!$event->getReceivePayments() || !$event->isHaveFreeTickets()) {
            return $this->render(
                '@ApplicationDefault/Page/index.html.twig',
                ['text' => $this->get('translator')->trans('error.payment.closed', ['%event%' => $event->getName()])]
            );
        }

        return $this->render('@ApplicationDefault/Redesign/Payment/payment.html.twig', ['event' => $event]);
    }

    /**
     * @Route("/event/{eventSlug}/payment/participant/edit/{id}/{name}/{surname}/{email}", name="edit_ticket_participant",
     *     methods={"POST"},
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @ParamConverter("event", options={"mapping": {"eventSlug": "slug"}})
     *
     * @param Event  $event
     * @param Ticket $ticket
     * @param string $name
     * @param string $surname
     * @param string $email
     *
     * @return JsonResponse
     */
    public function editTicketParticipantAction(Event $event, Ticket $ticket, $name, $surname, $email): JsonResponse
    {
        if (!$event->getReceivePayments() || !$event->isHaveFreeTickets()) {
            return new JsonResponse(
                [
                    'result' => false,
                    'error' => $this->get('translator')->trans('error.payment.closed', ['%event%' => $event->getName()]),
                ]
            );
        }

        $payment = $this->getPaymentIfAccess();
        if ($payment->isPaid()) {
            return new JsonResponse(['result' => false, 'error' => 'Payment paid!', 'html' => '']);
        }

        $ticketPayment = $ticket->getPayment();
        if (!$ticketPayment instanceof Payment || $ticketPayment->getId() !== $payment->getId()) {
            return new JsonResponse(['result' => false, 'error' => 'Bad ticket!']);
        }

        $session = $this->get('session');

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

                return new JsonResponse(['result' => false, 'error' => 'Bad credentials!']);
            }
        } else {
            if (\in_array($user->getId(), $newUsersList, true)) {
                try {
                    $userManager->updateUserData($user, $name, $surname, $email);
                } catch (BadCredentialsException $e) {
                    $this->get('logger')->addError('autoRegistration with bad params');

                    return new JsonResponse(['result' => false, 'error' => 'Bad credentials!']);
                }
            }
        }

        $oldUser = $ticket->getUser();
        if ($user instanceof User && $user->getId() !== $oldUser->getId()) {
            $oldUser->removeTicket($ticket);
            $user->addTicket($ticket);
        }
        $this->get('app.payment.service')->checkTicketsPricesInPayment($payment, $event);

        $paymentData = $this->get('serializer')->normalize($payment, null, ['groups' => ['payment.view']]);
        $ticketData = $this->get('serializer')->normalize($ticket, null, ['groups' => ['payment.view']]);

        return new JsonResponse(['result' => true, 'payment_data' => $paymentData, 'ticket_data' => $ticketData]);
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
    public function addParticipantToPaymentAction(Event $event, $name, $surname, $email): JsonResponse
    {
        if (!$event->getReceivePayments() || !$event->isHaveFreeTickets()) {
            return new JsonResponse(
                [
                    'result' => false,
                    'error' => $this->get('translator')->trans('error.payment.closed', ['%event%' => $event->getName()]),
                ]
            );
        }

        $payment = $this->getPaymentIfAccess();

        if (!$payment) {
            return new JsonResponse(['result' => false, 'error' => 'Payment not found or access denied!', 'html' => '']);
        }

        if ($payment->isPaid()) {
            return new JsonResponse(['result' => false, 'error' => 'Payment paid!', 'html' => '']);
        }
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

                return new JsonResponse(['result' => false, 'error' => 'Bad credentials!']);
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
        }

        if (!$ticket->isPaid()) {
            $paymentService = $this->get('app.payment.service');
            $paymentService->addTicketToPayment($payment, $ticket);
            $paymentService->checkTicketsPricesInPayment($payment, $event);
        } else {
            return new JsonResponse(
                [
                    'result' => false,
                    'error' => $this->get('translator')->trans('error.user.already.paid', ['%email%' => $user->getEmail()]),
                    'html' => '',
                ]
            );
        }

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
     * @Route("/payment-data", name="get_payment_data",
     *     methods={"GET"},
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @return JsonResponse
     */
    public function getPaymentDataAction(): JsonResponse
    {
        $payment = $this->getPaymentIfAccess();
        if (!$payment instanceof Payment) {
            return new JsonResponse(['result' => false, 'error' => 'Payment not found or access denied!']);
        }

        $result = $this->get('serializer')->normalize($payment, null, ['groups' => ['payment.view']]);

        return new JsonResponse(['result' => true, 'payment_data' => $result]);
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
     * Get payment html for popup.
     *
     * @param Event          $event
     * @param Payment        $payment
     * @param Promocode|null $promoCode
     *
     * @return JsonResponse
     */
    private function getPaymentHtml(Event $event, Payment $payment, PromoCode $promoCode = null)
    {
        $paymentsConfig = $this->container->getParameter('application_default.config');
        $discountAmount = 100 * (float) $paymentsConfig['discount'];

        $notUsedPromoCode = [];
        $paymentService = $this->get('app.payment.service');

        if (!$promoCode) {
            $promoCode = $paymentService->getPromoCodeFromPaymentTickets($payment);
        }
        if ($promoCode) {
            $notUsedPromoCode = $paymentService->addPromoCodeForTicketsInPayment($payment, $promoCode);
        }

        $paySystemData = $this->get('app.way_for_pay.service')->getData($payment, $event);

        $formAction = null;
        $payType = null;

        $byeBtnCaption = $this->get('translator')->trans('ticket.status.pay');
        /** @var User */
        $user = $this->getUser();
        if ($payment->getTickets()->count() > 0) {
            if (0 === (int) $payment->getAmount()) {
                $formAction = $payment->getFwdaysAmount() > 0 ?
                    $this->generateUrl('event_pay_by_bonus', ['eventSlug' => $event->getSlug()]) : $this->generateUrl('event_pay_by_promocode', ['eventSlug' => $event->getSlug()]);
                $byeBtnCaption = $this->get('translator')->trans('ticket.status.get');
            } else {
                $payType = WayForPayService::WFP_PAY_BY_SECURE_PAGE;
                $formAction = WayForPayService::WFP_SECURE_PAGE;
            }
        }

        $html = $this->renderView('@ApplicationDefault/Redesign/Payment/wayforpay.html.twig', [
            'params' => $paySystemData,
            'event' => $event,
            'payment' => $payment,
            'discountAmount' => $discountAmount,
            'pay_type' => $payType,
        ]);

        if (\in_array($payType, [WayForPayService::WFP_PAY_BY_SECURE_PAGE, WayForPayService::WFP_PAY_BY_WIDGET, true])) {
            $this->get('session')->set('way_for_pay_payment', $payment->getId());
        }

        return new JsonResponse([
            'result' => true,
            'not_used_promo_code' => $notUsedPromoCode,
            'phone_number' => $user->getPhone(),
            'is_user_create_payment' => $user === $payment->getUser(),
            'form_action' => $formAction,
            'pay_type' => $payType,
            'bye_btn_caption' => $byeBtnCaption,
        ]);
    }

    /**
     * Check if payment correct and give it by id.
     *
     * @param Ticket $removeTicket
     *
     * @return Payment|null $payment
     */
    private function getPaymentIfAccess($removeTicket = null)
    {
        $payment = null;
        if ($this->get('session')->has(self::ACTIVE_PAYMENT_ID_KEY)) {
            $paymentId = $this->get('session')->get(self::ACTIVE_PAYMENT_ID_KEY);
            $em = $this->getDoctrine()->getManager();
            $payment = $em->getRepository('ApplicationDefaultBundle:Payment')->find($paymentId);
        }

        if ($removeTicket instanceof Ticket) {
            $payment = $payment && ($removeTicket->getUser()->getId() === $this->getUser()->getId() ||
                $payment->getUser()->getId() === $this->getUser()->getId()) ? $payment : null;
        } else {
            $payment = $payment && $payment->getUser()->getId() === $this->getUser()->getId() ? $payment : null;
        }

        return $payment;
    }

    /**
     * @param Event   $event
     * @param Payment $payment
     * @param string  $code
     *
     * @throws \Exception
     *
     * @return PromoCode|null
     */
    private function getPromoCodeFromQuery(Event $event, Payment $payment, $code)
    {
        $promoCode = null;
        if (!$payment->isPaid()) {
            $em = $this->getDoctrine()->getManager();
            $promoCode = $em->getRepository('ApplicationDefaultBundle:PromoCode')
                ->findActivePromoCodeByCodeAndEvent($code, $event);
            if (($promoCode && !$promoCode->isCanBeUsed()) || is_array($promoCode)) {
                $promoCode = null;
            }
        }

        return $promoCode;
    }
}
