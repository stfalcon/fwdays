<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\Review;
use Application\Bundle\DefaultBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * WidgetsController.
 */
class WidgetsController extends Controller
{
    /**
     * Like review.
     *
     * @Route(path="/like/{slug}", name="like_review",
     *     methods={"POST"},
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     * @Security("has_role('ROLE_USER')"))
     *
     * @param Review $review
     *
     * @return JsonResponse
     */
    public function likeAction(Review $review)
    {
        $user = $this->getUser();

        if ($user instanceof User) {
            if ($review->isLikedByUser($user)) {
                $review->removeLikedUser($user);
            } else {
                $review->addLikedUser($user);
            }
            $this->getDoctrine()->getManager()->flush();
        }

        return new JsonResponse(['result' => true, 'likesCount' => $review->getLikedUsers()->count()]);
    }
}
