<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Symfony\Component\HttpFoundation\RedirectResponse,
    JMS\SecurityExtraBundle\Annotation\Secure;
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
     * @Route("/generate/")
     *
    */
    //public function getTicketQRAction(Ticket $ticket){
    public function getTicketQRAction(){
        //$user   = $ticket->getUser();
        //$fio    = $user->getFullname();
        //$hash   = md5($ticket->getId().$ticket->getCreatedAt());
        //$userId->$user->getId();
        //$eventId    = $ticket->getEvent();
        $eventId = 1;
        $userId = 2;
        $fio = 'Makuhin Vital Vitalivick';
        $hash = md5($fio);
        $qrCode = $this->get('stfalcon_event.qr_code');
        $qrCode->setText('http://frameworksdays.com/verify/'.$eventId.'/'.$userId.'/'.$hash);
        $qrCode = $qrCode->get();
        //$ticketPrint = imagecreatetruecolor(320,240);

        //imagestring ($ticketPrint, 50, 5, 5,"A Simple Text String", 1);

        //ImageTTFtext($ticketPrint, 26, 0, 200, 40, 0, "Times", "Simona");

        //imagecopy($ticketPrint, $qrCode, 50, 50, 0, 0, 100, 100);
        //imagepng($ticketPrint);

        return new \Symfony\Component\HttpFoundation\Response($qrCode, 200, array('Content-Type' => 'image/png'));
    }

    /**
     * @Route("/verify/{hash}")
     *
     */
    public function verifyQRAction ($eventId, $userId, $hash){

        $em     = $this->getDoctrine()->getManager();
        $ticket = $em->getRepository('StfalconEventBundle:Ticket')
            ->findOneBy(
            array(
                'event' => $eventId,
                'user' => $userId
            )
        );

        if ($ticket->isUsed()) throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('ticked is used at'.$ticket->getUpdateAt());

        $ticketHash = md5($ticket->getId().$ticket->getCreatedAt());

        if ($ticketHash == $hash){

             if (false){//if admin

                  $ticket->setUsed();
                 //set registration date
                 $em->flush();
             }
             else {
                // show information about user and Event
             }
        }
        else throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('User or ticked not found');

    }
}
