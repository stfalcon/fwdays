<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\EventPage;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
//        $partners = $partnerRepository->getSponsorsOfEvent($event);

        $partners = $partnerRepository->getSponsorsOfEventWithCategory($event);

        $sortedPartners = [];
        foreach ($partners as $key => $partner){
            $sortedPartners[$partner['isWideContainer']][$partner['name']][] = $partner[0];
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
     * @Route(path="/get_modal_header/{slug}", name="get_modal_header",
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     * @param $slug
     * @return JsonResponse
     */
    public function getModalHeaderAction($slug)
    {
        $event = $this->getEventBySlug($slug);
        if (!$event) {
            return new JsonResponse(['result' => false, 'error' => 'Unable to find Event by slug: '.$slug]);
        }

        $html = $this->get('translator')->trans('popup.header.title', ['%event_name%' => $event->getName()]);

        return new JsonResponse(['result' => true, 'error' => '', 'html' => $html]);
    }
    /**
     * Юзер бажає відвідати подію
     *
     * @Route(path="/addwantstovisitevent/{slug}", name="add_wants_to_visit_event",
     *     options = {"expose"=true})
     * @Security("has_role('ROLE_USER')")
     *
     * @param string $slug
     * @param Request $request
     *
     * @return JsonResponse|Response
     */
    public function userAddWantsToVisitEventAction($slug, Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $result = false;
        $html = '';
        $em = $this->getDoctrine()->getManager();
        $event = $this->getEventBySlug($slug);

        if (!$event) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['result' => $result, 'error' => 'Unable to find Event by slug: ' . $slug]);
            } else {
                return $this->createNotFoundException('Unable to find Event by slug: ' . $slug);
            }
        }

        $result = false;

        if ($event->isActiveAndFuture()) {
            $result = $user->addWantsToVisitEvents($event);
            $error = $result ? '' : 'cant remove event '.$slug;
        } else {
            $error = 'Event not active!';
        }

        if ($result) {
            $html = $this->get('translator')->trans('ticket.status.not_take_apart');
            $em->flush();
        }
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['result' => $result, 'error' => $error, 'html' => $html]);
        } else {
            $url = $request->headers->has('referer') ? $request->headers->get('referer')
                : $this->generateUrl('homepage_redesign');

            return $this->redirect($url);
        }
    }

    /**
     * Юзер вже не бажає відвідати подію
     *
     * @Route("/subwantstovisitevent/{slug}", name="sub_wants_to_visit_event",
     *     methods={"POST"},
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     * @Security("has_role('ROLE_USER')")
     *
     * @param string $slug
     *
     * @return JsonResponse
     */
    public function userSubWantsToVisitEventAction($slug)
    {
        /** @var User $user */
        $user = $this->getUser();
        $result = false;
        $html = '';
        $em = $this->getDoctrine()->getManager();
        $event = $this->getEventBySlug($slug);

        if (!$event) {
            return new JsonResponse(['result' => $result, 'error' => 'Unable to find Event by slug: '.$slug]);
        }

        if ($event->isActiveAndFuture()) {
            $result = $user->subtractWantsToVisitEvents($event);
            $error = $result ? '' : 'cant remove event '.$slug;
        } else {
            $error = 'Event not active!';
        }

        if ($result) {
            $html = $this->get('translator')->trans('ticket.status.take_apart');
            $em->flush();
        }

        return new JsonResponse(['result' => $result, 'error' => $error, 'html' => $html]);
    }

    /**
     * Get event entity by slug
     *
     * @param string $slug
     * @throw \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Event
     */
    private function getEventBySlug($slug)
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