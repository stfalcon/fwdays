<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Stfalcon\Bundle\EventBundle\Entity\Ticket;

/**
 * Event controller
 */
class EventController extends BaseController
{
    
    /**
     * List of past and future events
     *
     * @Route("/events", name="events")
     * @Template()
     * @return array
     */
    public function indexAction()
    {
        // @todo refact. отдельнымы спискамм активные и прошедние ивенты
        $events = $this->getDoctrine()->getEntityManager()
                       ->getRepository('StfalconEventBundle:Event')->findAll();

        return array('events' => $events);
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
        $event = $this->getEventBySlug($event_slug);

        // пользователь авторизирован
        $user = $this->container->get('security.context')->getToken()->getUser();

        /*
        // пользователь не авторизирован
            // я зарегистрированный пользователь
                // вводит e-mail и пароль
                // создаем билет
            // я новый пользователь
                // <<< регистрируем пользователя
                // на e-mail отправляем ссылку для активации аккаунта >>>
                // создаем билет
        */

        // проверяем или у него нет билетов на этот ивент
        $em = $this->getDoctrine()->getEntityManager();
        $ticket = $em->getRepository('StfalconEventBundle:Ticket')->findOneBy(array('event' => $event->getId(), 'user' => $user->getId()));

        // если нет, тогда создаем билет
        if (is_null($ticket)) {
            $ticket = new Ticket();
            $ticket->setUser($user);
            $ticket->setEvent($event);
            $ticket->setStatus(Ticket::STATUS_NEW);

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
        $user = $this->container->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getEntityManager();
        $tickets = $em->getRepository('StfalconEventBundle:Ticket')->findBy(array('user' => $user->getId()));

        return array('tickets' => $tickets);
    }    

}
