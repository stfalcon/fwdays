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
    
    /**
     * @Template()
     */
    public function sliderAction()
    {
        $events = $this->getDoctrine()->getEntityManager()
                     ->getRepository('StfalconEventBundle:Event')
                     ->findBy(array('active' => true ));
        
        return array('events' => $events);
    }

    /**
     * 
     * @Route("/events/payment-status", name="events_payment_status")
     * @Template()
     * @return array
     */
    public function statusAction()
    {
        $paidEvents = $this->getDoctrine()->getEntityManager()
                       ->getRepository('StfalconEventBundle:Ticket')
                       ->findAllPaid();

        return compact('paidEvents');
    }
    
}
