<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentController extends BaseController
{

    /**
     * Event pay
     *
     * @param Event $event
     * @throws \Exception
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/event/{event_slug}/pay", name="event_pay")
     * @ParamConverter("event", options={"mapping": {"event_slug": "slug"}})
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function payAction(Event $event)
    {
        if (!$event->getReceivePayments()) {
            throw new \Exception("Оплата за участие в {$event->getName()} не принимается.");
        }

        /* @var $ticket Ticket */
        $ticket = $this->container->get('stfalcon_event.ticket.service')
            ->findTicketForEventByCurrentUser($event);

        $paymentService = $this->get('stfalcon_event.payment.service');

        if (!$payment = $ticket->getPayment()) {
            $payment = $paymentService->createPaymentForCurrentUserWithTicket($ticket);
        }

        if ($payment->isPaid()) {
            return new RedirectResponse($this->generateUrl('events_my'));
        }

        if ($payment->isPending()) {
            $paymentService->checkTicketsPricesInPayment($payment, $event);
            $paymentService->payByReferralMoney($payment);
        }

        /**
         * Обработка формы промо кода
         */
        $promoCodeForm = $this->createForm('stfalcon_event_promo_code');

        $promoCode = $paymentService->getPromoCodeFromPaymentTickets($payment);
        $request = $this->container->get('request_stack')->getCurrentRequest();

        if ($request->isMethod('post')) {
            $promoCodeForm->submit($request);
            $code = $promoCodeForm->get('code')->getData();

            $em = $this->getDoctrine()->getManager();
            $promoCode = $em->getRepository('StfalconEventBundle:PromoCode')
                ->findActivePromoCodeByCodeAndEvent($code, $event);

            if ($promoCode) {
                $notUsedPromoCode = $paymentService->addPromoCodeForTicketsInPayment($payment, $promoCode);
                if (!empty($notUsedPromoCode)) {
                    $this->get('session')->getFlashBag()->add('not_used_promocode', implode(', ', $notUsedPromoCode));
                }

            } else {
                $promoCodeForm->get('code')->addError(new FormError('Такой промокод не найден'));
            }
        }

        $this->get('session')->set('active_payment_id', $payment->getId());

        /* @var  User $user */
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $paymentsConfig = $this->container->getParameter('stfalcon_event.config');
        $discountAmount = 100 * (float)$paymentsConfig['discount'];

        return array(
            'data'           => $this->get('stfalcon_event.interkassa.service')->getData($payment, $event),
            'event'          => $event,
            'payment'        => $payment,
            'promoCodeForm'  => $promoCodeForm->createView(),
            'promoCode'      => $promoCode,
            'ticketForm'     => $this->createForm('stfalcon_event_ticket')->createView(),
            'discountAmount' => $discountAmount,
            'balance'        => $user->getBalance(),
            'promoCodeFromTickets' => $paymentService->getPromoCodeFromPaymentTickets($payment),
        );
    }


    /**
     * Добавления участников к платежу
     *
     * @param string $slug
     *
     * @return RedirectResponse
     *
     * @Route("/event/{slug}/payment/participants/add", name="add_participants_to_payment")
     */
    public function addParticipantsToPaymentAction($slug)
    {
        $event = $this->getEventBySlug($slug);
        $em = $this->getDoctrine()->getManager();

        $paymentId = $this->get('session')->get('active_payment_id', null);

        if (!is_null($paymentId)) {
            $payment = $em->getRepository('StfalconEventBundle:Payment')->find($paymentId);
        } else {
            throw $this->createNotFoundException('Unable to find payment');
        }

        if ($payment->isPaid()) {
            throw new HttpException(404, sprintf('Can not allow paid'));
        }

        $request = $this->container->get('request_stack')->getCurrentRequest();
        $ticketForm = $this->createForm('stfalcon_event_ticket');
        $ticketForm->submit($request);

        $participants = $ticketForm->get('participants')->getData();
        $alreadyPaidTickets = [];

        foreach ($participants as $participant) {
            $user = $this->get('fos_user.user_manager')->findUserBy(['email' => $participant['email']]);

            // создаем нового пользователя
            if (!$user) {
                $user = $this->get('fos_user.user_manager')->autoRegistration($participant);
            }

            // проверяем или у него нет билетов на этот ивент
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
            } else {
                $alreadyPaidTickets[] = $user->getFullname();
            }
        }

        if (!empty($alreadyPaidTickets)) {
            $this->get('session')->getFlashBag()->add('already_paid_tickets', implode(', ', $alreadyPaidTickets));
        }

        return $this->redirect($this->generateUrl('event_pay', array('event_slug' => $event->getSlug())));
    }

    /**
     *
     * Удаления билета на события в платеже
     *
     * @param string $event_slug
     * @param int    $payment_id
     * @param Ticket $ticket
     *
     * @return RedirectResponse
     * @throws NotFoundHttpException
     *
     * @Route("/event/{event_slug}/payment/{payment_id}/ticket/{id}/remove", name="remove_ticket_from_payment")
     */
    public function removeTicketFromPaymentAction($event_slug, $payment_id, Ticket $ticket)
    {
        $event = $this->getEventBySlug($event_slug);

        $em = $this->getDoctrine()->getManager();
        $paymentRepository = $em->getRepository('StfalconEventBundle:Payment');
        $payment = $paymentRepository->find($payment_id);

        if (!$payment) {
            throw $this->createNotFoundException('Unable to find Payment entity.');
        }

        $this->get('stfalcon_event.payment.service')->removeTicketFromPayment($payment, $ticket);

        return $this->redirect($this->generateUrl('event_pay', ['event_slug' => $event->getSlug()]));
    }
}
