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
     * @param Event $event
     *
     * @return array
     *
     * @Route("/event/{slug}/participants", name="event_participants")
     * @Template()
     */
    public function indexAction(Event $event)
    {
        return $this->getParticipants($event);
    }

    /**
     * List of participants ajax
     *
     * @param Event $event
     * @param int   $offset
     *
     * @return array
     *
     * @Route("/event/{slug}/participants/{offset}", name="event_list_participants")
     * @Template("StfalconEventBundle:Participant:list_participants.html.twig")
     */
    public function listParticipantsAction(Event $event, $offset)
    {
        return $this->getParticipants($event, $offset);
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

        $participants = $ticketRepository->findTicketsByEventGroupByUser($event, $count);

        if ($count > 1) {
            shuffle($participants);
            $participants = array_slice($participants, 0, $count);
        }

        return array(
            'event' => $event,
            'participants' => $participants
        );
    }

    /**
     * Get participants
     *
     * @param Event $event
     * @param int   $offset
     *
     * @return array
     */
    protected function getParticipants(Event $event, $offset = null)
    {
        /** @var $ticketRepository \Stfalcon\Bundle\EventBundle\Repository\TicketRepository */
        $ticketRepository = $this->getDoctrine()->getManager()->getRepository('StfalconEventBundle:Ticket');

        $participants = $ticketRepository->findTicketsByEventGroupByUser($event, 20, $offset);

        $this->container->set('stfalcon_event.current_event', $event);

        return array(
            'event' => $event,
            'participants' => $participants
        );
    }
}
