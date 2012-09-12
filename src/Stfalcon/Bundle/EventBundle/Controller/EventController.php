<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Stfalcon\Bundle\PaymentBundle\Entity\Payment;

/**
 * Event controller
 */
class EventController extends BaseController
{
    /**
     * List of active and past events
     *
     * @return array
     *
     * @Route("/events", name="events")
     * @Template()
     */
    public function indexAction()
    {
        $activeEvents = $this->getDoctrine()->getManager()
                     ->getRepository('StfalconEventBundle:Event')
                     ->findBy(array('active' => true ));

        $pastEvents = $this->getDoctrine()->getManager()
                     ->getRepository('StfalconEventBundle:Event')
                     ->findBy(array('active' => false ));

        return array(
            'activeEvents' => $activeEvents,
            'pastEvents'   => $pastEvents
        );
    }

    /**
     * Finds and displays a Event entity.
     *
     * @param string $eventSlug
     *
     * @return array
     *
     * @Route("/event/{eventSlug}", name="event_show")
     * @Template()
     */
    public function showAction($eventSlug)
    {
        $event = $this->getEventBySlug($eventSlug);

        return array('event' => $event);
    }

    /**
     * Take part in the event. Create new ticket for user
     *
     * @param string $eventSlug
     *
     * @return RedirectResponse
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/event/{eventSlug}/take-part", name="event_takePart")
     * @Template()
     */
    public function takePartAction($eventSlug)
    {
        $em    = $this->getDoctrine()->getManager();
        $event = $this->getEventBySlug($eventSlug);
        $user  = $this->container->get('security.context')->getToken()->getUser();

        // проверяем или у него нет билетов на этот ивент
        $ticket = $em->getRepository('StfalconEventBundle:Ticket')
                     ->findOneBy(array('event' => $event->getId(), 'user' => $user->getId()));

        // если нет, тогда создаем билет
        if (is_null($ticket)) {
            $ticket = new Ticket($event, $user);
            $em->persist($ticket);
            $em->flush();
        }

        // переносим на страницу билетов пользователя к хешу /evenets/my#zend-framework-day-2011
        return new RedirectResponse($this->generateUrl('events_my') . '#' . $event->getSlug());
    }

    /**
     * Show user's only active events.
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

        return array('tickets' => $tickets);
    }

    /**
     * Event pay
     *
     * @param string $eventSlug
     *
     * @return array
     * @throws \Exception
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/event/{eventSlug}/pay", name="event_pay")
     * @Template()
     */
    public function payAction($eventSlug)
    {
        $event = $this->getEventBySlug($eventSlug);

        if (!$event->getReceivePayments()) {
            throw new \Exception("Оплата за участие в {$event->getName()} не принимается.");
        }

        $em   = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $ticket = $this->getDoctrine()->getManager()
                       ->getRepository('StfalconEventBundle:Ticket')
                       ->findOneBy(array('event' => $event->getId(), 'user'  => $user->getId()));

        // создаем проплату или апдейтим стоимость уже существующей
        if ($payment = $ticket->getPayment()) {
            // здесь может быть проблема. например клиент проплатил через банк и платеж идет к
            // шлюзу несколько дней. если обновить цену в этот момент, то сума платежа
            // может не соответствовать цене
//            $payment->setAmount($event->getAmount());
//            $em->persist($payment);
        } else {
            $payment = new Payment($user, $event->getAmount());
            $em->persist($payment);
            $ticket->setPayment($payment);
            $em->persist($ticket);
        }

        $em->flush();

        return $this->forward('StfalconPaymentBundle:Interkassa:pay', array('user' => $user, 'payment' => $payment));
    }
}
