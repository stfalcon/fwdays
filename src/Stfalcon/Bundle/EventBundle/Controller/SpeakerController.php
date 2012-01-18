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
     * @Route("/event/{event_slug}/speakers", name="event_speakers")
     * @Template()
     */
    public function indexAction($event_slug)
    {

        $event = $this->getEventBySlug($event_slug);
        $speakers = $event->getSpeakers();

        return array('event' => $event, 'speakers' => $speakers);
    }

    /**
     * List of speakers for event
     *
     * @Template()
     *
     * @param Event $event
     * @param integer $count
     * @return array
     */
    public function widgetAction(Event $event, $count)
    {
        $speakers = $event->getSpeakers()->getValues();

        if ($count > 1) {
            shuffle($speakers);
            $speakers = array_slice($speakers, 0, $count);
        }

        return array('event' => $event, 'speakers' => $speakers);
    }

}
