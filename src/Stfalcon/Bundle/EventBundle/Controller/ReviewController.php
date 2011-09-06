<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\EventBundle\Entity\Review;

/**
 * Review controller
 */
class ReviewController extends BaseController
{
//    /**
//     * Lists all sreakers for event
//     *
//     * @Route("/event/{event_slug}/speakers", name="event_speakers")
//     * @Template()
//     */
//    public function indexAction($event_slug)
//    {
//        
//        $event = $this->getEventBySlug($event_slug);
//        $speakers = $event->getReviews();
//
//        return array('event' => $event, 'speakers' => $speakers);
//    }

    /**
     * Finds and displays a Review entity.
     *
     * @Route("/event/{event_slug}/review/{review_slug}", name="event_review_show")
     * @Template()
     */
    public function showAction($event_slug, $review_slug)
    {
        $event = $this->getEventBySlug($event_slug);
        
        $em = $this->getDoctrine()->getEntityManager();
        $review = $em->getRepository('StfalconEventBundle:Review')->findOneBy(array('slug' => $review_slug));
        
        if (!$review) {
            throw $this->createNotFoundException('Unable to find Review entity.');
        }
        
        return array(
            'event' => $event, 'review' => $review,
        );
    }
    
}
