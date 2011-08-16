<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\EventBundle\Entity\Event;

/**
 * Event controller
 */
class EventController extends BaseController
{
    /**
     * Lists all Event entities.
     *
     * @Route("/events", name="events")
     * @Template()
     */
    public function indexAction()
    {
        $events = $this->getDoctrine()->getEntityManager()
                       ->getRepository('StfalconEventBundle:Event')->findAll();

        return array('events' => $events);
    }

    /**
     * Finds and displays a Event entity.
     *
     * @Route("/event/{slug}", name="event_show")
     * @Template()
     */
    public function showAction(Event $event)
    {
        $this->setEventToContainer($event);
        
        return array('event' => $event);
    }
    
}
