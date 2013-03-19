<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
        return array('events' => $this->_getActiveEvents());
    }

    /**
     * Panel for managing event slider's slide switching
     *
     * @Template()
     * @return array
     */
    public function switchAction()
    {
        return array('events' => $this->_getActiveEvents());
    }

    /**
     * Get array of active events
     *
     * @return array
     */
    private function _getActiveEvents()
    {
        $events = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')
            ->findBy(array('active' => true ));

        return $events;
    }

}