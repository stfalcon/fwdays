<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Symfony\Component\Form\FormError;

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
     * @return array
     */
    public function payAction(Event $event)
    {
        if (!$event->getReceivePayments()) {
            throw new \Exception("Оплата за участие в {$event->getName()} не принимается.");
        }

        $em = $this->getDoctrine()->getManager();

        /* @var $user User */
        $user = $this->container->get('security.context')->getToken()->getUser();

        /* @var $ticket Ticket */
        $ticket = $this->container->get('stfalcon_event.ticket.service')
            ->findTicketForEventByCurrentUser($event);

        if (!$payment = $ticket->getPayment()) {
            $payment = new Payment();
            $payment->setUser($user);
            $em->persist($payment);
            $payment->addTicket($ticket);
            $em->persist($ticket);
        }

        if (!$payment->isPaid()) {
            $this->get('stfalcon_event.payment_manager')
                ->checkTicketsPricesInPayment($payment, $event->getCost());
        }

        $em->flush();

        /**
         * Обработка формы промо кода
         */
        $promoCodeForm = $this->createForm('stfalcon_event_promo_code');

        $promoCode = $payment->getPromoCodeFromTickets();
        $request = $this->getRequest();

        // процент скидки для постоянных участников
        $paymentsConfig = $this->container->getParameter('stfalcon_event.config');
        $discountAmount = 100 * (float)$paymentsConfig['discount'];

        if ($request->isMethod('post')) {
            $promoCodeForm->bind($request);
            $code = $promoCodeForm->get('code')->getData();
            $promoCode = $em->getRepository('StfalconEventBundle:PromoCode')
                ->findActivePromoCodeByCodeAndEvent($code, $event);

            if ($promoCode) {
                $notUsedPromoCode = $payment->addPromoCodeForTickets($promoCode, $discountAmount);
                $em->flush();

                if (!empty($notUsedPromoCode)) {
                    $this->get('session')->getFlashBag()->add('not_used_promocode', implode(', ', $notUsedPromoCode));
                }

            } else {
                $promoCodeForm->get('code')->addError(new FormError('Такой промокод не найден'));
            }
        }

        return array(
            'data' => $this->get('stfalcon_event.interkassa.service')->getData($payment, $event),
            'event' => $event,
            'payment' => $payment,
            'promoCodeForm' => $promoCodeForm->createView(),
            'promoCode' => $promoCode,
            'ticketForm' => $this->createForm('stfalcon_event_ticket')->createView(),
            'discountAmount' => $discountAmount
        );
    }


    /**
     * Добавления участников к платежу
     *
     * @param string $slug
     * @param Payment $payment
     *
     * @return RedirectResponse
     *
     * @Route("/event/{slug}/payment/{id}/participants/add", name="add_participants_to_payment")
     */
    public function addParticipantsToPaymentAction($slug, Payment $payment)
    {
        $event = $this->getEventBySlug($slug);
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $ticketForm = $this->createForm('stfalcon_event_ticket');
        $ticketForm->bind($request);

        $participants = $ticketForm->get('participants')->getData();
        $alreadyPaidTickets = array();

        foreach ($participants as $participant) {
            $user = $this->get('fos_user.user_manager')->findUserBy(array('email' => $participant['email']));

            // создаем нового пользователя
            if (!$user) {
                $user = $this->get('fos_user.user_manager')->autoRegistration($participant);
            }

            // проверяем или у него нет билетов на этот ивент
            /** @var Ticket $ticket */
            $ticket = $em->getRepository('StfalconEventBundle:Ticket')
                ->findOneBy(array('event' => $event->getId(), 'user' => $user->getId()));

            if (!$ticket) {
                $ticket = $this->container->get('stfalcon_event.ticket.service')
                    ->createTicket($event, $user);
            }

            if (!$ticket->isPaid()) {
                if (($promoCode = $payment->getPromoCodeFromTickets()) && !$ticket->getHasDiscount()) {
                    $ticket->setPromoCode($promoCode);
                }
                $ticket->setPayment($payment);
            } else {
                $alreadyPaidTickets[] = $user->getFullname();
            }
            $em->persist($payment);
            $em->persist($ticket);
        }

        $em->flush();
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
     * @param int $payment_id
     * @param Ticket $ticket
     *
     * @return array
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

        // а якщо чувак оплатив квиток?
        $payment->removeTicket($ticket);
        $em->remove($ticket);
        $em->flush();

        return $this->redirect($this->generateUrl('event_pay', array('event_slug' => $event->getSlug())));
    }

}
