<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\User;
use App\Exception\BadAutoRegistrationDataException;
use App\Form\Type\ParticipantFormType;
use App\Model\UserManager;
use App\Service\PaymentProcess\AbstractPaymentProcessService;
use App\Service\PaymentProcess\PaymentProcessInterface;
use App\Service\PaymentService;
use App\Service\Ticket\TicketService;
use App\Traits;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
class PaymentController extends AbstractController
{
    use Traits\TranslatorTrait;
    use Traits\SerializerTrait;
    use Traits\SessionTrait;
    use Traits\LoggerTrait;

    private $paymentService;
    private $paymentSystem;
    private $userManager;
    private $ticketService;

    /**
     * @param PaymentService          $paymentService
     * @param PaymentProcessInterface $paymentSystem
     * @param UserManager             $userManager
     * @param TicketService           $ticketService
     */
    public function __construct(PaymentService $paymentService, PaymentProcessInterface $paymentSystem, UserManager $userManager, TicketService $ticketService)
    {
        $this->paymentService = $paymentService;
        $this->paymentSystem = $paymentSystem;
        $this->userManager = $userManager;
        $this->ticketService = $ticketService;
    }

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
                'Page/index.html.twig',
                ['text' => $this->translator->trans('error.payment.closed', ['%event%' => $event->getName()])]
            );
        }

        $payment = $this->paymentService->getPaymentForCurrentUser($event);

        $result = $this->serializer->normalize($payment, null, ['groups' => ['payment.view']]);

        return $this->render(
            'Redesign/Payment/payment.html.twig',
            [
                'event' => $event,
                'payment_data' => $result,
                'with_conditions' => $this->paymentSystem->isAgreeWithConditionsRequired(),
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
                    'error' => ['user_name' => $this->translator->trans('error.payment.closed', ['%event%' => $event->getName()])],
                ]
            );
        }
        $payment = $this->paymentService->getPendingPaymentIfAccess($event, $ticket);
        if (!$payment instanceof Payment) {
            return new JsonResponse(['result' => false, 'error' => ['user_name' => 'Payment not found or access denied!']]);
        }

        $form = $this->createForm(ParticipantFormType::class);
        $form->submit($request->request->all());
        try {
            $formUser = $this->userManager->getUserFromForm($form);
        } catch (BadAutoRegistrationDataException $e) {
            $this->logger->addError('Bad user data!');

            return new JsonResponse(['result' => false, 'error' => $e->getErrorMap()]);
        }

        $promoCodeString = \trim(\strip_tags($form->get('promocode')->getData()));
        $this->session->set(self::NEW_PAYMENT_SESSION_KEY, false);

        /** @var User|null $user */
        $user = $this->userManager->findUserBy(['email' => $formUser->getEmail()]);

        if ($this->ticketService->isUserHasPaidTicketForEvent($user, $event)) {
            return new JsonResponse(
                [
                    'result' => false,
                    'error' => ['email' => $this->translator->trans('error.user.already.paid', ['%email%' => $user->getEmail()])],
                ]
            );
        }

        try {
            $ticket = $this->paymentService->replaceIfFindOtherUserTicketForEvent($user, $event, $ticket);
        } catch (BadRequestHttpException $e) {
            return new JsonResponse(
                [
                    'result' => false,
                    'error' => ['email' => $this->translator->trans('error.ticket.already.added')],
                ]
            );
        }

        $newUsersList = $this->session->get(UserManager::NEW_USERS_SESSION_KEY, []);
        if (!$user && \in_array($ticket->getUser()->getId(), $newUsersList, true)) {
            $user = $ticket->getUser();
        }

        try {
            if (!$user) {
                $user = $this->userManager->autoRegistration($formUser);
            } else {
                $this->userManager->updateUserData($user, $formUser);
            }
        } catch (BadAutoRegistrationDataException $e) {
            return new JsonResponse(['result' => false, 'error' => $e->getErrorMap()]);
        } catch (BadRequestHttpException $e) {
            return new JsonResponse(['result' => false, 'error' => ['email' => $this->translator->trans('error.user.cant_be_edit')]]);
        }

        $this->ticketService->setNewUserToTicket($user, $ticket);
        $user->addWantsToVisitEvents($event);
        try {
            $this->paymentService->addPromoCodeForTicketByCode($promoCodeString, $event, $ticket);
        } catch (BadRequestHttpException $e) {
            return new JsonResponse(['result' => false, 'error' => ['user_promo_code' => $e->getMessage()]]);
        }

        $this->paymentService->checkTicketsPricesInPayment($payment, $event);

        $paymentData = $this->serializer->normalize($payment, null, ['groups' => ['payment.view']]);
        $ticketData = $this->serializer->normalize($ticket, null, ['groups' => ['payment.view']]);

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
                    'error' => $this->translator->trans('error.payment.closed', ['%event%' => $event->getName()]),
                    'path' => 'user_name',
                ]
            );
        }
        $payment = $this->paymentService->getPendingPaymentIfAccess($event);
        if (!$payment) {
            return new JsonResponse(['result' => false, 'error' => ['user_name' => 'Payment not found or access denied!']]);
        }
        $form = $this->createForm(ParticipantFormType::class, null, ['csrf_protection' => false]);
        $form->submit($request->request->all());

        try {
            $formUser = $this->userManager->getUserFromForm($form);
        } catch (BadAutoRegistrationDataException $e) {
            $this->logger->addError('Bad user data!');

            return new JsonResponse(['result' => false, 'error' => $e->getErrorMap()]);
        }

        $promoCodeString = \trim(\strip_tags($form->get('promocode')->getData()));

        /** @var User|null $user */
        $user = $this->userManager->findUserBy(['email' => $formUser->getEmail()]);
        if (!$user) {
            try {
                $user = $this->userManager->autoRegistration($formUser);
            } catch (BadAutoRegistrationDataException $e) {
                $this->logger->addError('autoRegistration with bad params');

                return new JsonResponse(['result' => false, 'error' => $e->getErrorMap()]);
            }
        }

        $em = $this->getDoctrine()->getManager();

        /** @var Ticket|null $ticket */
        $ticket = $em->getRepository(Ticket::class)
            ->findOneBy(['event' => $event->getId(), 'user' => $user->getId()]);

        if (!$ticket) {
            $ticket = $this->ticketService->createTicket($event, $user);
            $user->addWantsToVisitEvents($event);
            $em->flush();
        } else {
            if ($ticket->isPaid()) {
                return new JsonResponse(
                    [
                        'result' => false,
                        'error' => ['email' => $this->translator->trans('error.user.already.paid', ['%email%' => $user->getEmail()])],
                    ]
                );
            }

            if ($payment->getTickets()->contains($ticket)) {
                return new JsonResponse(
                    [
                        'result' => false,
                        'error' => ['email' => $this->translator->trans('error.ticket.already.added')],
                    ]
                );
            }
        }

        $this->paymentService->addTicketToPayment($payment, $ticket);
        try {
            $this->paymentService->addPromoCodeForTicketByCode($promoCodeString, $event, $ticket);
        } catch (BadRequestHttpException $e) {
            return new JsonResponse(['result' => false, 'error' => ['user_promo_code' => $e->getMessage()]]);
        }

        $this->paymentService->checkTicketsPricesInPayment($payment, $event);

        $paymentData = $this->serializer->normalize($payment, null, ['groups' => ['payment.view']]);
        $ticketData = $this->serializer->normalize($ticket, null, ['groups' => ['payment.view']]);

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
        $payment = $this->paymentService->getPendingPaymentIfAccess($event, $ticket);
        if (!$payment) {
            return new JsonResponse(['result' => false, 'error' => 'Payment not found or access denied!', 'html' => '']);
        }

        if ($payment->getTickets()->count() > 1) {
            $this->paymentService->removeTicketFromPayment($payment, $ticket);
            $this->paymentService->checkTicketsPricesInPayment($payment, $event);
        } else {
            return new JsonResponse(['result' => false, 'error' => 'Must be at least one ticket!', 'html' => '']);
        }

        $result = $this->serializer->normalize($payment, null, ['groups' => ['payment.view']]);

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
        $payment = $this->paymentService->getPendingPaymentIfAccess($event);

        if ($payment && 0.0 === $payment->getAmount()) {
            $result = $this->paymentService->setPaidByBonusMoney($payment, $event);
        }

        if ($result) {
            $this->session->set(AbstractPaymentProcessService::SESSION_PAYMENT_KEY, $payment->getId());

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
        $payment = $this->paymentService->getPendingPaymentIfAccess($event);

        if ($payment && 0.0 === $payment->getAmount()) {
            $result = $this->paymentService->setPaidByPromocode($payment, $event);
        }

        if ($result) {
            $this->session->set(AbstractPaymentProcessService::SESSION_PAYMENT_KEY, $payment->getId());

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
        $payment = $this->paymentService->getPendingPaymentIfAccess($event);
        if (!$payment instanceof Payment) {
            return new JsonResponse(['result' => false, 'error' => 'Payment not found or access denied!']);
        }

        $amount = (float) $request->query->get('amount', 0);

        $this->paymentService->addFwdaysBonusToPayment($payment, $amount);
        $result = $this->serializer->normalize($payment, null, ['groups' => ['payment.view']]);

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
        $payment = $this->paymentService->getPendingPaymentIfAccess($event);
        if (!$payment instanceof Payment) {
            return new JsonResponse(['result' => false, 'error' => 'Payment not found or access denied!']);
        }

        $this->paymentService->checkTicketsPricesInPayment($payment, $event);

        $savedData = (float) $request->request->get('saved_data');
        $paymentData = $this->serializer->normalize($payment, null, ['groups' => ['payment.view']]);

        $form = null;
        $amountChanged = $savedData !== $paymentData['amount'];
        if ($amountChanged) {
            $paymentData['amount_changed_text'] = $this->translator->trans('pay.amount_changed');
        } else {
            $formAction = null;
            $payType = null;
            $paySystemData = null;
            if ($payment->getTickets()->count() > 0) {
                if (0.0 === $payment->getAmount()) {
                    $formAction = $payment->getFwdaysAmount() > 0 ?
                        $this->generateUrl('event_pay_by_bonus', ['slug' => $event->getSlug()]) : $this->generateUrl('event_pay_by_promocode', ['slug' => $event->getSlug()]);
                } else {
                    $formAction = $this->paymentSystem->getFormAction();
                    $paySystemData = $this->paymentSystem->getData($payment, $event);
                }
            }

            $form = $this->renderView('Redesign/Payment/pay_form.html.twig', [
                'params' => $paySystemData,
                'action' => $formAction,
            ]);
        }

        return new JsonResponse(['result' => true, 'amount_changed' => $amountChanged, 'payment_data' => $paymentData, 'form' => $form]);
    }
}
