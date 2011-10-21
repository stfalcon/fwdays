<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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

}
