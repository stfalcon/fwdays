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
        // @todo refact. отдельнымы спискамм активные и прошедние ивенты
        $events = $this->getDoctrine()->getEntityManager()
                       ->getRepository('StfalconEventBundle:Event')->findAll();

        return array('events' => $events);
    }

    /**
     * Finds and displays a Event entity.
     *
     * @Route("/event/{event_slug}", name="event_show")
     * @Template()
     */
    public function showAction($event_slug)
    {
        var_dump($event_slug);
        exit;
        $this->setEventToContainer($event);
        
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
    
}
