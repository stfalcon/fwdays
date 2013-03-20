<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Review controller
 */
class ReviewController extends BaseController
{

    /**
     * Finds and displays a Review
     *
     * @Route("/event/{event_slug}/review/{review_slug}", name="event_review_show")
     * @Template()
     */
    public function showAction($event_slug, $review_slug)
    {
        $event = $this->getEventBySlug($event_slug);

        $review = $this->getDoctrine()->getRepository('StfalconEventBundle:Review')->findOneBy(array('slug' => $review_slug));

        if (!$review) {
            throw $this->createNotFoundException('Unable to find Review entity.');
        }

        return array(
            'event' => $event, 'review' => $review,
        );
    }

}
