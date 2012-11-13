<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Stfalcon\Bundle\EventBundle\Entity\Event;

/**
 * Speaker controller
 */
class SpeakerController extends BaseController
{
    /**
     * Lists all speakers for event
     *
     * @param string $event_slug
     *
     * @return array
     *
     * @Route("/event/{event_slug}/speakers", name="event_speakers")
     * @Template()
     */
    public function indexAction($event_slug)
    {
        $event = $this->getEventBySlug($event_slug);

        /** @var $speakerRepository \Stfalcon\Bundle\EventBundle\Repository\SpeakerRepository */
        $speakerRepository = $this->getDoctrine()->getManager()->getRepository('StfalconEventBundle:Speaker');

        /** @var $reviewRepository \Stfalcon\Bundle\EventBundle\Repository\ReviewRepository */
        $reviewRepository = $this->getDoctrine()->getManager()->getRepository('StfalconEventBundle:Review');

        $speakers = $speakerRepository->findSpeakersForEvent($event_slug);

        /** @var $speaker \Stfalcon\Bundle\EventBundle\Entity\Speaker */
        foreach ($speakers as &$speaker) {
            $speaker->setReviews(
                $reviewRepository->findReviewsOfSpeakerForEvent($speaker, $event)
            );
        }

        return array(
            'event'    => $event,
            'speakers' => $speakers
        );
    }

    /**
     * List of speakers for event
     *
     * @param Event $event Event
     * @param int   $count Count
     *
     * @return array
     *
     * @Template()
     */
    public function widgetAction(Event $event, $count)
    {
        $speakers = $event->getSpeakers()->getValues();

        if ($count > 1) {
            shuffle($speakers);
            $speakers = array_slice($speakers, 0, $count);
        }

        return array(
            'event' => $event,
            'speakers' => $speakers
        );
    }
}
