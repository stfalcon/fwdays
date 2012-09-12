<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Stfalcon\Bundle\EventBundle\Entity\Event;

/**
 * Speaker controller
 */
class SpeakerController extends BaseController
{
    /**
     * Lists all sreakers for event
     *
     * @param string $eventSlug
     *
     * @return array
     *
     * @Route("/event/{event_slug}/speakers", name="event_speakers")
     * @Template()
     */
    public function indexAction($eventSlug)
    {
        $event = $this->getEventBySlug($eventSlug);
        $speakers = $event->getSpeakers();

        return array(
            'event'    => $event,
            'speakers' => $speakers
        );
    }

    /**
     * List of speakers for event
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
        $speakers = $event->getSpeakers()->getValues();

        if ($count > 1) {
            shuffle($speakers);
            $speakers = array_slice($speakers, 0, $count);
        }

        return array(
            'event'    => $event,
            'speakers' => $speakers
        );
    }
}
