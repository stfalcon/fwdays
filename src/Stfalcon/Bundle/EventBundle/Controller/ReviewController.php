<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Review controller
 */
class ReviewController extends BaseController
{
    /**
     * Finds and displays a Review
     *
     * @param string $eventSlug  Event slug
     * @param string $reviewSlug Review slug
     *
     * @return array
     * @throws NotFoundHttpException
     *
     * @Route("/event/{event_slug}/review/{review_slug}", name="event_review_show")
     * @Template()
     */
    public function showAction($eventSlug, $reviewSlug)
    {
        $event = $this->getEventBySlug($eventSlug);

        $em = $this->getDoctrine()->getManager();
        $review = $em->getRepository('StfalconEventBundle:Review')->findOneBy(array('slug' => $reviewSlug));

        if (!$review) {
            throw $this->createNotFoundException('Unable to find Review entity.');
        }

        return array(
            'event'  => $event,
            'review' => $review,
        );
    }
}
