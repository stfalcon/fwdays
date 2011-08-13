<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\StfalconBundle\Entity\Page;

/**
 * Page controller.
 */
class PageController extends Controller
{
    /**
     * Finds and displays a Page entity.
     *
     * @Route("/event/{event_slug}/page/{page_slug}", name="event_page_show")
     * @Template()
     */
    public function showAction($event_slug, $page_slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $event = $em->getRepository('StfalconEventBundle:Event')->findOneBy(array('slug' => $event_slug));
        $page = $em->getRepository('StfalconEventBundle:Page')->findOneBy(array('slug' => $page_slug, 'event' => $event->getId()));

        if (!$page) {
            throw $this->createNotFoundException('Unable to find Page entity.');
        }

        return array('page' => $page, 'event' => $event);
    }
    
}