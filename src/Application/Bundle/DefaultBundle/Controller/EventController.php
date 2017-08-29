<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\EventPage;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Application event controller
 */
class EventController extends Controller
{
    /**
     * @Route("/events", name="events_redesign")
     * @Template("ApplicationDefaultBundle:Redesign:events.html.twig")
     *
     * @return array
     */
    public function eventsAction()
    {
        $activeEvents = $this->getDoctrine()->getManager()
            ->getRepository('StfalconEventBundle:Event')
            ->findBy(['active' => true], ['date' => 'ASC']);

        $pastEvents = $this->getDoctrine()->getManager()
            ->getRepository('StfalconEventBundle:Event')
            ->findBy(['active' => false], ['date' => 'DESC']);

        return [
            'activeEvents' => $activeEvents,
            'pastEvents' => $pastEvents,
        ];
    }

    /**
     * Finds and displays a Event entity.
     *
     * @Route("/event/{event_slug}", name="event_show_redesign")
     * @param string $event_slug
     * @Template("ApplicationDefaultBundle:Redesign:event.html.twig")
     *
     * @return array
     */
    public function showAction($event_slug)
    {
        $referralService = $this->get('stfalcon_event.referral.service');
        $referralService->handleRequest($this->container->get('request_stack')->getCurrentRequest());

        return $this->getEventPagesArr($event_slug);
    }

    /**
     * Finds and displays a Review
     *
     * @Route("/event/{event_slug}/review/{review_slug}", name="event_review_show_redesign")
     * @param string $event_slug
     * @param string $review_slug
     * @Template("ApplicationDefaultBundle:Redesign:report_review.html.twig")
     *
     * @return array
     */
    public function showEventReviewAction($event_slug, $review_slug)
    {
        return $this->getEventPagesArr($event_slug, $review_slug);
    }

    /**
     * Return array of event with pages
     *
     * @param string      $event_slug
     * @param string|null $review_slug
     *
     * @return array
     */
    private function getEventPagesArr($event_slug, $review_slug = null)
    {
        $event = $this->getEventBySlug($event_slug);
        if (!$event) {
            throw $this->createNotFoundException('Unable to find event by slug: '.$event_slug);
        }
        $review = null;
        if ($review_slug) {
            $review = $this->getDoctrine()->getRepository('StfalconEventBundle:Review')->findOneBy(['slug' => $review_slug]);

            if (!$review) {
                throw $this->createNotFoundException('Unable to find Review entity.');
            }
        }

        /** @var ArrayCollection $pages */
        $pages = $this->getEventPages($event);

        /** @var EventPage $page */
        $programPage = null;
        $venuePage = null;
        foreach ($pages as $key => $page) {
            if ('program' == $page->getSlug()) {
                $programPage = $page;
                unset($pages[$key]);
            } elseif ('venue' == $page->getSlug()) {
                $venuePage = $page;
                unset($pages[$key]);
            }
        }

        return [
            'event'       => $event,
            'programPage' => $programPage,
            'venuePage'   => $venuePage,
            'pages'       => $pages,
            'review'      => $review,
        ];
    }

    /**
     * Get event entity by slug
     *
     * @param string $slug
     * @throw \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Event
     */
    public function getEventBySlug($slug)
    {
        $event = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')->findOneBy(['slug' => $slug]);

        if (!$event) {
            throw $this->createNotFoundException('Unable to find Event by slug: '.$slug);
        }

        $this->setEventToContainer($event);

        return $event;
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

    /**
     * List of sponsors of event
     *
     * @param Event $event
     * @Template("ApplicationDefaultBundle:Redesign:partners.html.twig")
     * @return array
     *
     */
    public function eventPartnersAction(Event $event)
    {

        /** @var $partnerRepository \Stfalcon\Bundle\SponsorBundle\Repository\SponsorRepository */
        $partnerRepository = $this->getDoctrine()->getManager()
            ->getRepository('StfalconSponsorBundle:Sponsor');
        $partners = $partnerRepository->getSponsorsOfEvent($event);

        $sortedPartners = [];
        foreach ($partners as $partner){
            $sortedPartners[$partner['category_name']][] = $partner['sponsor'];
        }

        return ['partners' => $sortedPartners];
    }

    /**
     * Set event entity to DI container
     *
     * @param Event $event
     */
    public function setEventToContainer(Event $event)
    {
        // this value used in EventSubMenu
        $this->container->set('stfalcon_event.current_event', $event);
    }

    /**
     * Events slider (block)
     *
     * @Template()
     * @return array
     */
    public function sliderAction()
    {
        return ['events' => $this->_getActiveEvents()];
    }

    /**
     * Panel for managing event slider's slide switching
     *
     * @Template()
     * @return array
     */
    public function switchAction()
    {
        return ['events' => $this->_getActiveEvents()];
    }

    /**
     * Get array of active events
     *
     * @return array
     */
    private function _getActiveEvents()
    {
        $events = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')
            ->findBy(['active' => true]);

        return $events;
    }

    /**
     * Get event pages that may show
     *
     * @param Event $event
     * @return array
     */
    private function getEventPages(Event $event)
    {
        $pages = [];
        /** @var EventPage $page */
        foreach ($event->getPages() as $page) {
            if ($page->isShowInMenu()) {
                $pages[] = $page;
            }
        }

        return $pages;
    }
}