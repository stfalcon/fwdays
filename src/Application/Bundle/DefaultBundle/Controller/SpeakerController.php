<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class SpeakerController.
 */
class SpeakerController extends Controller
{
    /**
     * @Route(path="/speaker_popup/{eventSlug}/{speakerSlug}", name="speaker_popup",
     *     methods={"GET"},
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     *
     * @param string $speakerSlug
     * @param string $eventSlug
     *
     * @return JsonResponse
     */
    public function speakerPopupAction($speakerSlug, $eventSlug)
    {
        $em = $this->getDoctrine()->getManager();

        $speaker = $em->getRepository('StfalconEventBundle:Speaker')->findOneBy(['slug' => $speakerSlug]);
        if (!$speaker) {
            return new JsonResponse(['result' => false, 'html' => 'Unable to find Speaker by slug: '.$speakerSlug]);
        }

        $event = $em->getRepository('StfalconEventBundle:Event')->findOneBy(['slug' => $eventSlug]);
        if (!$event) {
            return new JsonResponse(['result' => false, 'html' => 'Unable to find Event by slug: '.$eventSlug]);
        }
        /** @var $reviewRepository \Stfalcon\Bundle\EventBundle\Repository\ReviewRepository */
        $reviewRepository = $this->getDoctrine()->getManager()->getRepository('StfalconEventBundle:Review');
        $speaker->setReviews(
            $reviewRepository->findReviewsOfSpeakerForEvent($speaker, $event)
        );
        $html = $this->renderView('@ApplicationDefault/Redesign/Speaker/speaker.popup.html.twig', [
            'speaker' => $speaker,
            'event' => $event,
        ]);

        return new JsonResponse(['result' => true, 'html' => $html]);
    }

    /**
     * Lists all speakers for event.
     *
     * @param Event $event
     * @param bool  $isCandidates
     *
     * @Template("ApplicationDefaultBundle:Redesign/Speaker:speaker.html.twig")
     *
     * @return array
     */
    public function eventSpeakersAction(Event $event, $isCandidates = false)
    {
        /** @var $reviewRepository \Stfalcon\Bundle\EventBundle\Repository\ReviewRepository */
        $reviewRepository = $this->getDoctrine()->getManager()->getRepository('StfalconEventBundle:Review');

        if ($isCandidates) {
            $speakers = $event->getCandidateSpeakers();
        } else {
            $speakers = $event->getSpeakers();
        }

        /** @var $speaker \Stfalcon\Bundle\EventBundle\Entity\Speaker */
        foreach ($speakers as &$speaker) {
            $speaker->setReviews(
                $reviewRepository->findReviewsOfSpeakerForEvent($speaker, $event)
            );
        }

        return [
            'event' => $event,
            'speakers' => $speakers,
        ];
    }
}
