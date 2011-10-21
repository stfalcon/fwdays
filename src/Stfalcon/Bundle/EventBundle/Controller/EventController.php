<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Event controller
 */
class EventController extends BaseController
{

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

}
