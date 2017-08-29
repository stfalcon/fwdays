<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Stfalcon\Bundle\EventBundle\Entity\Review;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Review controller
 * @Route("/old")
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

    /**
     * Like review
     *
     * @param Review $review
     *
     * @return JsonResponse|RedirectResponse
     *
     * @Route("/review/{slug}/like", name="event_review_like")
     *
     * @Secure(roles="ROLE_USER")
     */
    public function likeAction(Review $review)
    {
        $user = $this->getUser();
        if ($review->isLikedByUser($user)) {
            $review->removeLikedUser($user);
        } else {
            $review->addLikedUser($user);
        }
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new JsonResponse(array('likesCount' => $review->getLikedUsers()->count()));
        }

        return $this->redirect($this->generateUrl('event_speakers', array('event_slug' => $review->getEvent()->getSlug())));
    }
}
