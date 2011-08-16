<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\EventBundle\Entity\Page;
use Stfalcon\Bundle\EventBundle\Entity\Event;

/**
 * Page controller
 */
class PageController extends BaseController
{
    /**
     * Finds and displays a Page entity.
     *
     * @Route("/event/{event_slug}/page/{page_slug}", name="event_page_show")
     * @Template()
     */
    public function showAction($event_slug, $page_slug)
    {
        $event = $this->getEventBySlug($event_slug);
        
        $page = $this->getDoctrine()->getEntityManager()
                     ->getRepository('StfalconEventBundle:Page')
                     ->findOneBy(array('event' => $event->getId(), 'slug' => $page_slug));
        
        if (!$page) {
            throw $this->createNotFoundException('Unable to find Page entity.');
        }

        return array('event' => $event, 'page' => $page);
    }
    
}