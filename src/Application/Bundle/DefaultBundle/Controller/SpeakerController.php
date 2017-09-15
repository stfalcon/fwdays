<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SpeakerController extends Controller
{
    /**
     * @Route(path="/speaker_popup/{event_slug}/{speaker_slug}", name="speaker_popup",
     *     methods={"GET"},
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     * @param string $speaker_slug
     * @param string $event_slug
     *
     * @return JsonResponse
     */
    public function speakerPopupAction($speaker_slug, $event_slug)
    {
        $em = $this->getDoctrine()->getManager();

        $speaker = $em->getRepository('StfalconEventBundle:Speaker')->findOneBy(['slug' => $speaker_slug]);
        if (!$speaker) {
            return new JsonResponse(['result' => false, 'html' => 'Unable to find Speaker by slug: '.$speaker_slug]);
        }

        $event = $em->getRepository('StfalconEventBundle:Event')->findOneBy(['slug' => $event_slug]);
        if (!$event) {
            return new JsonResponse(['result' => false, 'html' => 'Unable to find Event by slug: '.$event_slug]);
        }

        $html = $this->renderView('@ApplicationDefault/Redesign/speaker.popup.html.twig', [
            'speaker' => $speaker,
            'event' => $event,
        ]);

        return new JsonResponse(['result' => true, 'html' => $html]);
    }

    /**
     * Lists all speakers for event
     *
     * @param Event $event
     * @param bool $isCandidates
     * @Template("ApplicationDefaultBundle:Redesign:speaker.html.twig")
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
            'event'    => $event,
            'speakers' => $event->getSpeakers(),
        ];
    }
}
