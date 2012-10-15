<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Stfalcon\Bundle\PaymentBundle\Entity\Payment;

use Doctrine\ORM\EntityManager;

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
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $eventRepository \Stfalcon\Bundle\EventBundle\Repository\EventRepository */
        $eventRepository = $em->getRepository('StfalconEventBundle:Event');

        // Get active events
        $activeEvents = $eventRepository->findBy(array(
            'active' => true
        ));
        // Find past (not active) events
        $pastEvents = $eventRepository->findBy(array(
            'active' => false
        ));

        // Arrays with information about state of each event for some user: if user added, paid this event...
        $userActiveEventsInfo = array();
        $userPastEventsInfo   = array();

        // If this page was visited by authenticated user
        if (true === $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.context')->getToken()->getUser();

            /** @var $ticketRepository \Stfalcon\Bundle\EventBundle\Repository\TicketRepository */
            $ticketRepository = $this->getDoctrine()->getManager()->getRepository('StfalconEventBundle:Ticket');

            // Find user info for active events
            /** @var $activeEvent \Stfalcon\Bundle\EventBundle\Entity\Event */
            foreach ($activeEvents as $activeEvent) {
                $userAddedEvent = false;
                $userPaidEvent  = false;

                /** @var $userTicketForEvent \Stfalcon\Bundle\EventBundle\Entity\Ticket */
                $userTicketForEvent = $ticketRepository->findTicketOfUserForSomeActiveEvent($user, $activeEvent->getSlug());

                // If found user's ticket for this event, then event was added
                if ($userTicketForEvent) {
                    $userAddedEvent = true;
                    // If payment for this ticket was found and it has status 'paid', then event was paid
                    if ($userTicketForEvent->isPaid()) {
                        $userPaidEvent = true;
                    }
                }

                $userActiveEventsInfo[$activeEvent->getId()] = array(
                    'user_added_event' => $userAddedEvent,
                    'user_paid_event'  => $userPaidEvent
                );
            }

            // Find user info for past events
            /** @var $pastEvent \Stfalcon\Bundle\EventBundle\Entity\Event */
            foreach ($pastEvents as $pastEvent) {
                $userAddedEvent = false;
                $userPaidEvent  = false;

                /** @var $userTicketForEvent \Stfalcon\Bundle\EventBundle\Entity\Ticket */
                $userTicketForEvent = $ticketRepository->findTicketOfUserForSomeActiveEvent($user, $pastEvent->getSlug());

                // If found user's ticket for this event, then event was added
                if ($userTicketForEvent) {
                    $userAddedEvent = true;
                    // If payment for this ticket was found and it has status 'paid', then event was paid
                    if ($userTicketForEvent->isPaid()) {
                        $userPaidEvent = true;
                    }
                }

                $userPastEventsInfo[$pastEvent->getId()] = array(
                    'user_added_event' => $userAddedEvent,
                    'user_paid_event'  => $userPaidEvent
                );
            }
        }

        return array(
            'active_events'           => $activeEvents,
            'past_events'             => $pastEvents,
            'user_active_events_info' => $userActiveEventsInfo,
            'user_past_events_info'   => $userPastEventsInfo
        );
    }

    /**
     * Finds and displays a Event entity.
     *
     * @param string $event_slug
     *
     * @return array
     *
     * @Route("/event/{event_slug}", name="event_show")
     * @Template()
     */
    public function showAction($event_slug)
    {
        $userAddedEvent = false;
        $userPaidEvent  = false;

        if (true === $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.context')->getToken()->getUser();

            /** @var $ticketRepository \Stfalcon\Bundle\EventBundle\Repository\TicketRepository */
            $ticketRepository = $this->getDoctrine()->getManager()->getRepository('StfalconEventBundle:Ticket');

            /** @var $userTicketForEvent \Stfalcon\Bundle\EventBundle\Entity\Ticket */
            $userTicketForEvent = $ticketRepository->findTicketOfUserForSomeActiveEvent($user, $event_slug);

            // If found user's ticket for this event, then event was added
            if ($userTicketForEvent) {
                $userAddedEvent = true;
                // If payment for this ticket was found and it has status 'paid', then event was paid
                if ($userTicketForEvent->isPaid()) {
                    $userPaidEvent = true;
                }
            }
        }

        $event = $this->getEventBySlug($event_slug);

        return array(
            'event'            => $event,
            'user_added_event' => $userAddedEvent,
            'user_paid_event'  => $userPaidEvent
        );
    }

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

        $user = $this->get('security.context')->getToken()->getUser();

        if ($user instanceof \Application\Bundle\UserBundle\Entity\User) {
            // проверяем или у него нет билетов на этот ивент
            $ticket = $em->getRepository('StfalconEventBundle:Ticket')
                ->findOneBy(
                    array(
                         'event' => $event->getId(),
                         'user'  => $user->getId()
                    )
                );

            // если нет, тогда создаем билет
            if (is_null($ticket)) {
                $ticket = new Ticket($event, $user);
                $em->persist($ticket);
                $em->flush();
            }
        }

        // переносим на страницу билетов пользователя к хешу /evenets/my#zend-framework-day-2011
        return new RedirectResponse($this->generateUrl('events_my') . '#' . $event->getSlug());
    }

    /**
     * Show only active events of user
     *
     * @return array
     * @throws AccessDeniedException
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/events/my", name="events_my")
     * @Template()
     */
    public function myAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

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

        return $this->forward('StfalconPaymentBundle:Interkassa:pay', array(
            'user'    => $user,
            'payment' => $payment
        ));
    }
}
