<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Application\Bundle\DefaultBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class SpeakerController.
 */
class SpeakerController extends Controller
{
    const SPEAKER_TYPE_SPEAKER = 'speaker';
    const SPEAKER_TYPE_CANDIDATE = 'candidate';
    const SPEAKER_TYPE_COMMITTEE = 'committee';

    /**
     * @Route(path="/speaker_popup/{eventSlug}/{speakerSlug}/{withReview}", name="speaker_popup",
     *     methods={"GET"},
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     *
     * @param string   $speakerSlug
     * @param string   $eventSlug
     * @param bool|int $withReview
     *
     * @return JsonResponse
     */
    public function speakerPopupAction($speakerSlug, $eventSlug, $withReview = true)
    {
        $em = $this->getDoctrine()->getManager();

        $speaker = $em->getRepository('ApplicationDefaultBundle:Speaker')->findOneBy(['slug' => $speakerSlug]);
        if (!$speaker) {
            return new JsonResponse(['result' => false, 'html' => 'Unable to find Speaker by slug: '.$speakerSlug]);
        }

        $event = $em->getRepository('ApplicationDefaultBundle:Event')->findOneBy(['slug' => $eventSlug]);
        if (!$event) {
            return new JsonResponse(['result' => false, 'html' => 'Unable to find Event by slug: '.$eventSlug]);
        }

        if ((bool) $withReview) {
            /** @var $reviewRepository \Application\Bundle\DefaultBundle\Repository\ReviewRepository */
            $reviewRepository = $this->getDoctrine()->getManager()->getRepository('ApplicationDefaultBundle:Review');
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

//
//    /**
//     * Lists all speakers for event.
//     *
//     * @param Event  $event
//     * @param string $speakerType
//     *
//     * @Template("ApplicationDefaultBundle:Redesign/Speaker:speaker.html.twig")
//     *
//     * @return array
//     */
//    public function eventSpeakersAction(Event $event, $speakerType = self::SPEAKER_TYPE_SPEAKER)
//    {
//        switch ($speakerType) {
//            case self::SPEAKER_TYPE_SPEAKER:
//                $speakers = $event->getSpeakers();
//                break;
//            case self::SPEAKER_TYPE_CANDIDATE:
//                $speakers = $event->getCandidateSpeakers();
//                break;
//            case self::SPEAKER_TYPE_COMMITTEE:
//                $speakers = $event->getcommitteeSpeakers();
//        }
//
//        $withReview = false;
//
//        if (in_array($speakerType, [self::SPEAKER_TYPE_SPEAKER, self::SPEAKER_TYPE_CANDIDATE])) {
//            /** @var $reviewRepository \Application\Bundle\DefaultBundle\Repository\ReviewRepository */
//            $reviewRepository = $this->getDoctrine()->getManager()->getRepository('ApplicationDefaultBundle:Review');
//
//            /** @var $speaker \Application\Bundle\DefaultBundle\Entity\Speaker */
//            foreach ($speakers as &$speaker) {
//                $speaker->setReviews(
//                    $reviewRepository->findReviewsOfSpeakerForEvent($speaker, $event)
//                );
//            }
//
//            $withReview = true;
//        }
//
//        return [
//            'event' => $event,
//            'speakers' => $speakers,
//            'with_review' => $withReview,
//        ];
//    }
}
