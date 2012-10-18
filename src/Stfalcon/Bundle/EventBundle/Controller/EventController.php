<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Stfalcon\Bundle\PaymentBundle\Entity\Payment;

/**
 * Event controller
 */
class EventController extends BaseController
{

    /**
     * List of active and past events
     *
     * @return array
     *
     * @Route("/events", name="events")
     * @Template()
     */
    public function indexAction()
    {
        $activeEvents = $this->getDoctrine()->getManager()
                     ->getRepository('StfalconEventBundle:Event')
                     ->findBy(array('active' => true ));

        $pastEvents = $this->getDoctrine()->getManager()
                     ->getRepository('StfalconEventBundle:Event')
                     ->findBy(array('active' => false ));

        return array(
            'activeEvents' => $activeEvents,
            'pastEvents' => $pastEvents
        );
    }

    /**
     * Finds and displays a Event entity.
     *
     * @param string $event_slug
     *
     * @return array
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