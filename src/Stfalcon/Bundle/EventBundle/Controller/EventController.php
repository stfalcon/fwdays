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
     * @Route("/events", name="events")
     * @Template()
     * @return array
     */
    public function indexAction()
    {
        $activeEvents = $this->getDoctrine()->getEntityManager()
                     ->getRepository('StfalconEventBundle:Event')
                     ->findBy(array('active' => true ));

        $pastEvents = $this->getDoctrine()->getEntityManager()
                     ->getRepository('StfalconEventBundle:Event')
                     ->findBy(array('active' => false ));

        return array('activeEvents' => $activeEvents,
            'pastEvents' => $pastEvents);
    }

    /**
     * Finds and displays a Event entity.
     *
     * @Route("/event/{event_slug}", name="event_show")
     * @Template()
     */
    public function showAction($event_slug)
    {
        $event = $this->getEventBySlug($event_slug);

        return array('event' => $event);
    }

    /**
     * Take part in the event. Create new ticket for user
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/event/{event_slug}/take-part", name="event_takePart")
     * @Template()
     * @param string $event_slug
     * @return RedirectResponse
     */
    public function takePartAction($event_slug)
    {
        $em    = $this->getDoctrine()->getEntityManager();
        $event = $this->getEventBySlug($event_slug);
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
     * Show user events
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/events/my", name="events_my")
     * @Template()
     * @return array
     */
    public function myAction()
    {
        $user    = $this->container->get('security.context')->getToken()->getUser();
        $tickets = $this->getDoctrine()->getEntityManager()
                        ->getRepository('StfalconEventBundle:Ticket')->findBy(array('user' => $user->getId()));

        return array('tickets' => $tickets);
    }

    /**
     * Event pay
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/event/{event_slug}/pay", name="event_pay")
     * @Template()
     * @param string $event_slug
     * @return array
     */
    public function payAction($event_slug)
    {
        $event = $this->getEventBySlug($event_slug);

        if (!$event->getReceivePayments()) {
            throw new \Exception("Оплата за участие в {$event->getName()} не принимается.");
        }

        $em   = $this->getDoctrine()->getEntityManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $ticket = $this->getDoctrine()->getEntityManager()
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