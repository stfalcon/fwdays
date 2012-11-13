<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Symfony\Component\HttpFoundation\RedirectResponse,
    JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Stfalcon\Bundle\EventBundle\Entity\Ticket,
    Stfalcon\Bundle\EventBundle\Entity\Event,
    Stfalcon\Bundle\PaymentBundle\Entity\Payment;

/**
 * Ticket controller
 */
class TicketController extends BaseController
{

    /**
     * Take part in the event. Create new ticket for user
     *
     * @param string $event_slug
     *
     * @return RedirectResponse
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/event/{event_slug}/take-part", name="event_takePart")
     * @Template()
     */
    public function takePartAction($event_slug)
    {
        $em    = $this->getDoctrine()->getManager();
        $event = $this->getEventBySlug($event_slug);

        // проверяем или у него нет билетов на этот ивент
        $ticket = $this->_findTicketForEventByCurrentUser($event);

        // если нет, тогда создаем билет
        if (is_null($ticket)) {
            $ticket = new Ticket($event, $this->get('security.context')->getToken()->getUser());
            $em->persist($ticket);
            $em->flush();
        }

        // переносим на страницу билетов пользователя к хешу /evenets/my#zend-framework-day-2011
        return new RedirectResponse($this->generateUrl('events_my') . '#' . $event->getSlug());
    }

    /**
     * Show only active events of user
     *
     * @return array
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/events/my", name="events_my")
     * @Template()
     */
    public function myAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        /** @var $ticketRepository \Stfalcon\Bundle\EventBundle\Repository\TicketRepository */
        $ticketRepository = $this->getDoctrine()->getManager()
            ->getRepository('StfalconEventBundle:Ticket');
        $tickets = $ticketRepository->findTicketsOfActiveEventsForUser($user);

        return array(
            'tickets' => $tickets
        );
    }

    /**
     * Event pay
     *
     * @param string $event_slug
     *
     * @return array
     * @throws \Exception
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/event/{event_slug}/pay", name="event_pay")
     * @Template()
     */
    public function payAction($event_slug)
    {
        $event = $this->getEventBySlug($event_slug);

        if (!$event->getReceivePayments()) {
            throw new \Exception("Оплата за участие в {$event->getName()} не принимается.");
        }

        $em   = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $ticket = $this->_findTicketForEventByCurrentUser($event);

        // создаем проплату или апдейтим стоимость уже существующей
        if ($payment = $ticket->getPayment()) {
            // здесь может быть проблема. например клиент проплатил через банк и платеж идет к
            // шлюзу несколько дней. если обновить цену в этот момент, то сума платежа
            // может не соответствовать цене
//            $payment->setAmount($event->getAmount());
//            $em->persist($payment);
        } else {
            // Find paid payments for current user
            $paidPayments = $this->getDoctrine()->getManager()
                ->getRepository('StfalconPaymentBundle:Payment')
                ->findPaidPaymentsForUser($user);

            // Вытягиваем скидку из конфига
            $paymentsConfig = $this->container->getParameter('stfalcon_payment.config');
            $discount = (float) $paymentsConfig['discount'];

            // Если пользователь имеет оплаченные события, то он получает скидку
            if (count($paidPayments) > 0) {
                $cost = $event->getCost() - $event->getCost() * $discount;
                $hasDiscount = true;
            } else {
                $cost = $event->getCost();
                $hasDiscount = false;
            }

            $payment = new Payment($user, $cost, $hasDiscount);
            $payment->getUser();
            $payment->setAmountWithoutDiscount($event->getCost());
            $em->persist($payment);
            $ticket->setPayment($payment);
            $em->persist($ticket);
        }

        $em->flush();

        return $this->forward('StfalconPaymentBundle:Interkassa:pay', array(
            'user'    => $user,
            'payment' => $payment
        ));
    }

    /**
     * Show event ticket status (for current user)
     *
     * @param Event $event
     * @return array
     *
     * @Template()
     */
    public function statusAction(Event $event)
    {
        $ticket = $this->_findTicketForEventByCurrentUser($event);

        return array(
            'event' => $event,
            'ticket' => $ticket
        );
    }

    /**
     * Find ticket for event by current user
     *
     * @param Event $event
     * @return Ticket|null
     */
    private function _findTicketForEventByCurrentUser(Event $event)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $ticket = null;
        if (is_object($user) && $user instanceof \FOS\UserBundle\Model\UserInterface) {
            // проверяем или у пользователя есть билеты на этот ивент
            $ticket = $this->getDoctrine()->getManager()
                ->getRepository('StfalconEventBundle:Ticket')
                ->findOneBy(
                array(
                    'event' => $event->getId(),
                    'user' => $user->getId()
                )
            );
        }

        return $ticket;
    }

    /**
     * Generating ticket with  QR-code
     * to event
     *
     * @Secure(roles="ROLE_USER")
     *
     * @Route("/event/{event_slug}/ticket")
     * @param string $event_slug
     *
     * @return array
     *
     * @Template()
     */
    public function getTicketQRAction($event_slug)
    {
        $event = $this->getEventBySlug($event_slug);
        $ticket = $this->_findTicketForEventByCurrentUser($event);

        if ($ticket){
            $user = $ticket->getUser();

            $hash = md5($ticket->getId() . $ticket->getCreatedAt()->format('Y-m-d H:i:s'));
            $url = $this->generateUrl(
                 'verify_route', array(
                 'ticket' => $ticket->getId(),
                 'hash' => $hash), true);
            $qrCode = $this->get('stfalcon_event.qr_code');
            $qrCode->setText($url);
            $qrCode = base64_encode($qrCode->get());

            return array(
                 'qrcode' => $qrCode,
                 'fullname' => $user->getFullname(),
                 'id' => $user->getId()
            );
        }else{
            return new Response('You do not have Ticket');
        }
    }

    /**
     * Check that QR-code is valid, and register ticket
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/verify/{ticket}/{hash}", name="verify_route")
     * @param Ticket $ticket
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function verifyQRAction(Ticket $ticket, $hash)
    {

        $event = $ticket->getEvent();
        $user = $ticket->getUser();

        $ticketHash = md5($ticket->getId() . $ticket->getCreatedAt()->format('Y-m-d H:i:s'));

        //check hash sum
        if ($ticketHash != $hash) {
            throw new NotFoundHttpException();
        }

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $em = $this->getDoctrine()->getManager();

            if ($ticket->isUsed()) {
                throw new NotFoundHttpException(
                    'Ticked was used at ' . $ticket->getUpdatedAt()->format('Y-m-d H:i:s')
                );
            }

            //mark Ticket as used
            $ticket->setUsed(true);

            //set registration dateTime
            $updateAt = new \DateTime();
            $ticket->setUpdatedAt($updateAt);

            //update Ticket in database
            $em->flush();
            return new Response(
                'Success <br />' . $user->getFullname()
            );
        } else { // Ticket right, but it just user

            //return information about event and user
            return new Response(
                'Hi, ' . $user->getFullname() .
                    '<br /> ' . $event->getName() .
                    'Start at ' . $event->getDate()->format('Y-m-d') .
                    ' not oversleep :)'
            );
        }

    }
}
