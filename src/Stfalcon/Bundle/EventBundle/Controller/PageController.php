<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Page controller
 */
class PageController extends BaseController
{
    /**
     * Finds and displays a Page entity.
     *
     * @param string $eventSlug Event slug
     * @param string $pageSlug  Page slug
     *
     * @return array
     *
     * @Route("/event/{event_slug}/page/{page_slug}", name="event_page_show")
     * @Template()
     */
    public function showAction($eventSlug, $pageSlug)
    {
        $event = $this->getEventBySlug($eventSlug);

        $page = $this->getDoctrine()->getManager()
                     ->getRepository('StfalconEventBundle:Page')
                     ->findOneBy(array('event' => $event->getId(), 'slug' => $pageSlug));

        if (!$page) {
            throw $this->createNotFoundException('Unable to find Page entity.');
        }

        return array('event' => $event, 'page' => $page);
    }
}
