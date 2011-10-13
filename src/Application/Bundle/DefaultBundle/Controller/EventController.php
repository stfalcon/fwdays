<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * Application event controller
 */
class EventController extends Controller
{

    /**
     * List of past and future events
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

}
