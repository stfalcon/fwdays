<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Stfalcon\Bundle\EventBundle\Entity\Event;


/**
 * Participant controller
 */
class ParticipantController extends BaseController
{
    /**
     * Lists all speakers for event
     *
     * @param string $event_slug
     *
     * @return array
     *
     * @Route("/event/{event_slug}/participants", name="event_participants")
     * @Template()
     */
    public function indexAction($event_slug)
    {
        $event = $this->getEventBySlug($event_slug);

        /** @var $ticketRepository \Stfalcon\Bundle\EventBundle\Repository\TicketRepository */
        $ticketRepository = $this->getDoctrine()->getManager()->getRepository('StfalconEventBundle:Ticket');

        $participants = $ticketRepository->findTicketsByEventGroupByUser($event);

        return array(
            'event' => $event,
            'participants' => $participants
        );
    }

    /**
     * List of participants for event
     *
     * @param Event $event Event
     * @param int   $count Count
     *
     * @return array
     *
     * @Template()
     */
    public function widgetAction(Event $event, $count)
    {
        /** @var $ticketRepository \Stfalcon\Bundle\EventBundle\Repository\TicketRepository */
        $ticketRepository = $this->getDoctrine()->getManager()->getRepository('StfalconEventBundle:Ticket');

        $participants = $ticketRepository->findTicketsByEventGroupByUser($event);

        return array(
            'event' => $event,
            'participants' => $participants
        );
    }
}