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
     * Count participants
     */
    const COUNT_PARTICIPANTS = 20;

    /**
     * Lists all speakers for event
     *
     * @param string $event_slug Event slug
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

        $participants = $ticketRepository->findTicketsByEventGroupByUser($event, self::COUNT_PARTICIPANTS);

        return array(
            'event' => $event,
            'participants' => $participants
        );
    }

    /**
     * List of participants
     *
     * @param string $event_slug Event slug
     * @param int    $offset     Offset
     *
     * @return array
     *
     * @Route("/event/{event_slug}/participants/{offset}", name="event_list_participants")
     * @Template("StfalconEventBundle:Participant:list_participants.html.twig")
     */
    public function listParticipantsAction($event_slug, $offset)
    {
        $event = $this->getEventBySlug($event_slug);

        /** @var $ticketRepository \Stfalcon\Bundle\EventBundle\Repository\TicketRepository */
        $ticketRepository = $this->getDoctrine()->getManager()->getRepository('StfalconEventBundle:Ticket');

        $participants = $ticketRepository->findTicketsByEventGroupByUser($event, self::COUNT_PARTICIPANTS, $offset);

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

        $participants = $ticketRepository->findTicketsByEventGroupByUser($event, $count);

        return array(
            'event' => $event,
            'participants' => $participants
        );
    }
}
