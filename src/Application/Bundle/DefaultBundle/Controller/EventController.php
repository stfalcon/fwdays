<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Application event controller
 */
class EventController extends Controller
{
    /**
     * Events slider (block)
     *
     * @Template()
     * @return array
     */
    public function sliderAction()
    {
        $events = $this->getDoctrine()->getManager()
                     ->getRepository('StfalconEventBundle:Event')
                     ->findBy(array('active' => true));

        // Array with information about state of each event for some user: if user added, paid this event...
        $userEventsInfo = array();

        // If this page was visited by authenticated user
        if (true === $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.context')->getToken()->getUser();

            /** @var $ticketRepository \Stfalcon\Bundle\EventBundle\Repository\TicketRepository */
            $ticketRepository = $this->getDoctrine()->getManager()->getRepository('StfalconEventBundle:Ticket');

            /** @var $event \Stfalcon\Bundle\EventBundle\Entity\Event */
            foreach ($events as $event) {
                $userAddedEvent = false;
                $userPaidEvent  = false;

                /** @var $userTicketForEvent \Stfalcon\Bundle\EventBundle\Entity\Ticket */
                $userTicketForEvent = $ticketRepository->findTicketOfUserForSomeActiveEvent($user, $event->getSlug());

                // If found user's ticket for this event, then event was added
                if ($userTicketForEvent) {
                    $userAddedEvent = true;
                    // If payment for this ticket was found and it has status 'paid', then event was paid
                    if ($userTicketForEvent->isPaid()) {
                        $userPaidEvent = true;
                    }
                }

                $userEventsInfo[$event->getId()] = array(
                     'user_added_event' => $userAddedEvent,
                     'user_paid_event'  => $userPaidEvent
                );
            }
        }

        return array(
            'events'           => $events,
            'user_events_info' => $userEventsInfo
        );
    }
}
