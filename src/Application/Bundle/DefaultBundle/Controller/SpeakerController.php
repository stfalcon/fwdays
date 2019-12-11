<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Entity\Review;
use Application\Bundle\DefaultBundle\Entity\Speaker;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * SpeakerController.
 */
class SpeakerController extends Controller
{
    /**
     * @Route(path="/speaker_popup/{slug}/{speakerSlug}/{withReview}", name="speaker_popup",
     *     methods={"GET"},
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     *
     * @ParamConverter("speaker", class="ApplicationDefaultBundle:Speaker", options={"slug" = "speakerSlug"})
     *
     * @param Event    $event
     * @param Speaker  $speaker
     * @param bool|int $withReview
     *
     * @return JsonResponse
     */
    public function speakerPopupAction(Event $event, Speaker $speaker, $withReview = true): JsonResponse
    {
        if ((bool) $withReview) {
            $reviewRepository = $this->getDoctrine()->getRepository(Review::class);
            $speaker->setReviews(
                $reviewRepository->findReviewsOfSpeakerForEvent($speaker, $event)
            );
        }

        $html = $this->renderView('@ApplicationDefault/Redesign/Speaker/speaker.popup.html.twig', [
            'speaker' => $speaker,
            'event' => $event,
            'with_review' => $withReview,
        ]);

        return new JsonResponse(['result' => true, 'html' => $html]);
    }
}
