<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\Review;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class WidgetsController.
 */
class WidgetsController extends Controller
{
    /**
     * Like review.
     *
     * @Route(path="/like/{reviewSlug}", name="like_review",
     *     methods={"POST"},
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     * @Security("has_role('ROLE_USER')"))
     *
     * @ParamConverter("review", options={"mapping": {"reviewSlug": "slug"}})
     *
     * @param Review $review
     *
     * @return JsonResponse
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

        return new JsonResponse(['result' => true, 'likesCount' => $review->getLikedUsers()->count()]);
    }
}
