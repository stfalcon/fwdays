<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Form\EventType;

/**
 * Event controller.
 *
 */
class EventController extends Controller
{
    /**
     * Lists all Event entities.
     *
     * @Route("/events", name="events")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $events = $em->getRepository('StfalconEventBundle:Event')->findAll();

        return array('events' => $events);
    }

    /**
     * Finds and displays a Event entity.
     *
     * @Route("/event/{slug}", name="event_show")
     * @Template()
     */
    public function showAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $event = $em->getRepository('StfalconEventBundle:Event')->findOneBy(array('slug' => $slug));

        if (!$event) {
            throw $this->createNotFoundException('Unable to find Event entity.');
        }

        return array('event' => $event);
    }

}
