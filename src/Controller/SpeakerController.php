<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Review;
use App\Entity\Speaker;
use App\Repository\ReviewRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * SpeakerController.
 */
class SpeakerController extends AbstractController
{
    /**
     * @Route(path="/speaker_popup/{slug}/{speakerSlug}/{withReview}", name="speaker_popup",
     *     methods={"GET"},
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     *
     * @ParamConverter("speaker", class="AppBundle:Speaker", options={"mapping": {"speakerSlug": "slug"}})
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
            /** @var ReviewRepository $reviewRepository */
            $reviewRepository = $this->getDoctrine()->getRepository(Review::class);
            $speaker->setReviews(
                $reviewRepository->findReviewsOfSpeakerForEvent($speaker, $event)
            );
        }

        $html = $this->renderView('@App/Redesign/Speaker/speaker.popup.html.twig', [
            'speaker' => $speaker,
            'event' => $event,
            'with_review' => $withReview,
        ]);

        return new JsonResponse(['result' => true, 'html' => $html]);
    }
}
