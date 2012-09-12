<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Base controller
 */
class BaseController extends Controller
{

    /**
     * Get event entity by slug
     *
     * @param string $slug
     *
     * @return Event
     * @throws NotFoundHttpException
     */
    public function getEventBySlug($slug)
    {
        $event = $this->getDoctrine()->getManager()->getRepository('StfalconEventBundle:Event')->findOneBy(array('slug' => $slug));

        if (!$event) {
            throw $this->createNotFoundException('Unable to find Event entity.');
        }

        $this->setEventToContainer($event);

        return $event;
    }

    /**
     * Set event entity to DI container
     *
     * @param Event $event
     */
    public function setEventToContainer(Event $event)
    {
        // this value used in EventSubMenu
        $this->container->set('stfalcon_event.current_event', $event);
    }
}
