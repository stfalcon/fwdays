<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
//use Symfony\Component\HttpFoundation\Response;

/**
 * Application event controller
 */
class EventController extends Controller
{

    /**
     * Events slider (block)
     *
     * @Template()
     * @return array
     */
    public function sliderAction()
    {
        $events = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')
            ->findBy(array('active' => true));

        return array('events' => $events);
    }

    /**
     * Panel for managing event slider's slide switching
     *
     * @Template()
     * @return array
     */
    public function switchAction()
    {
        $events = $this->getDoctrine()->getEntityManager()
            ->getRepository('StfalconEventBundle:Event')
            ->findBy(array('active' => true ));

        return array('events' => $events);
    }

}