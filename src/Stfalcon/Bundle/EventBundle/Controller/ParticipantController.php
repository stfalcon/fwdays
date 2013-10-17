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
     * @param string $event_slug Event slug
     *
     * @return array
     *
     * @Route("/event/{event_slug}/participants/{offset}", name="event_participants", defaults={"offset"=0})
     * @Template("StfalconEventBundle:Participant:_list_of_participants.html.twig")
     */
    public function indexAction($event_slug, $offset)
    {
        $event = $this->getEventBySlug($event_slug);

        /** @var $ticketRepository \Stfalcon\Bundle\EventBundle\Repository\TicketRepository */
        $ticketRepository = $this->getDoctrine()->getManager()->getRepository('StfalconEventBundle:Ticket');

        $participants = $ticketRepository->findTicketsByEventGroupByUser($event, 20, $offset);

        return array(
            'request' => $this->getRequest(),
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
        $participants = $event->getTickets()->getValues();

        // @todo якось не так...
        if ($count > 1) {
            shuffle($participants);
            $participants = array_slice($participants, 0, $count);
        }

        return array(
            'event' => $event,
            'participants' => $participants
        );
    }
}
