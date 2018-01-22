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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Application event controller.
 */
class EventController extends Controller
{
    /**
     * Show all events.
     *
     * @Route("/events", name="events")
     *
     * @Template("@ApplicationDefault/Redesign/Event/events.html.twig")
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
     * Show event.
     *
     * @Route("/event/{eventSlug}", name="event_show_redesign")
     * @Route("/event/{eventSlug}", name="event_show")
     *
     * @param string $eventSlug
     *
     * @Template("@ApplicationDefault/Redesign/Event/event.html.twig")
     *
     * @return array
     */
    public function showAction($eventSlug)
    {
        $referralService = $this->get('stfalcon_event.referral.service');
        $referralService->handleRequest($this->container->get('request_stack')->getCurrentRequest());

        return $this->getEventPagesArr($eventSlug);
    }

    /**
     * Get event costs.
     *
     * @param Event $event
     *
     * @Template("@ApplicationDefault/Redesign/Event/event_price.html.twig")
     *
     * @return array
     */
    public function getEventCostsAction(Event $event)
    {
        $em = $this->getDoctrine()->getManager();
        $ticketCostRepository = $em->getRepository('ApplicationDefaultBundle:TicketCost');

        $eventCurrentCost = $ticketCostRepository->getEventCurrentCost($event);
        $ticketCosts = $ticketCostRepository->getEventTicketsCost($event);

        return [
            'ticketCosts' => $ticketCosts,
            'currentPrice' => $eventCurrentCost,
            'event' => $event,
        ];
    }

    /**
     * Finds and displays a event review.
     *
     * @Route("/event/{eventSlug}/review/{reviewSlug}", name="event_review_show_redesign")
     *
     * @param string $eventSlug
     * @param string $reviewSlug
     *
     * @Template("ApplicationDefaultBundle:Redesign/Speaker:report_review.html.twig")
     *
     * @return array
     */
    public function showEventReviewAction($eventSlug, $reviewSlug)
    {
        return $this->getEventPagesArr($eventSlug, $reviewSlug);
    }

    /**
     * List of sponsors of event.
     *
     * @param Event $event
     *
     * @Template("ApplicationDefaultBundle:Redesign/Partner:partners.html.twig")
     *
     * @return array
     */
    public function eventPartnersAction(Event $event)
    {
        /** @var $partnerRepository \Stfalcon\Bundle\SponsorBundle\Repository\SponsorRepository */
        $partnerRepository = $this->getDoctrine()->getManager()
            ->getRepository('StfalconSponsorBundle:Sponsor');
        $partnerCategoryRepository = $this->getDoctrine()->getManager()
            ->getRepository('StfalconSponsorBundle:Category');
        $partners = $partnerRepository->getSponsorsOfEventWithCategory($event);

        $sortedPartners = [];
        foreach ($partners as $key => $partner) {
            $partnerCategory = $partnerCategoryRepository->find($partner['id']);
            if ($partnerCategory) {
                $sortedPartners[$partnerCategory->isWideContainer()][$partnerCategory->getSortOrder()][$partnerCategory->getName()][] = $partner[0];
            }
        }

        if (isset($sortedPartners[0])) {
            krsort($sortedPartners[0]);
        }

        if (isset($sortedPartners[1])) {
            krsort($sortedPartners[1]);
        }

        return ['partners' => $sortedPartners];
    }

    /**
     * Get event header.
     *
     * @Route(path="/get_modal_header/{slug}/{headerType}", name="get_modal_header",
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     *
     * @param string $slug
     * @param string $headerType
     *
     * @return JsonResponse
     */
    public function getModalHeaderAction($slug, $headerType)
    {
        $event = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')->findOneBy(['slug' => $slug]);
        if (!$event) {
            return new JsonResponse(['result' => false, 'error' => 'Unable to find Event by slug: '.$slug]);
        }
        $html = '';
        if ('buy' === $headerType) {
            $html = $this->get('translator')->trans('popup.header.title', ['%event_name%' => $event->getName()]);
        } elseif ('reg' === $headerType) {
            $html = $this->get('translator')->trans('popup.header_reg.title', ['%event_name%' => $event->getName()]);
        }

        return new JsonResponse(['result' => true, 'error' => '', 'html' => $html]);
    }

    /**
     * Get event map position.
     *
     * @Route(path="/get_map_pos/{slug}", name="get_event_map_position",
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     *
     * @param string $slug
     *
     * @return JsonResponse
     */
    public function getEventMapPosition($slug)
    {
        $event = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')->findOneBy(['slug' => $slug]);
        if (!$event) {
            return new JsonResponse(['result' => false, 'error' => 'Unable to find Event by slug: '.$slug]);
        }
        $lat = 0;
        $lng = 0;

        $address = $event->getCity().','.$event->getPlace();
        $googleApiKey = $this->getParameter('google_api_key');
        $json = $this->container->get('buzz')->get(
            'https://maps.google.com/maps/api/geocode/json?key='.$googleApiKey.'&address='.urlencode($address)
        );
        $response = json_decode(
            $json->getContent(),
            true
        );

        if (isset($response['status']) && 'OK' === $response['status']) {
            $lat = isset($response['results'][0]['geometry']['location']['lat']) ? $response['results'][0]['geometry']['location']['lat'] : 0;
            $lng = isset($response['results'][0]['geometry']['location']['lng']) ? $response['results'][0]['geometry']['location']['lng'] : 0;
        } else {
            return new JsonResponse(['result' => false, 'lat' => $lat, 'lng' => $lng]);
        }

        return new JsonResponse(['result' => true, 'lat' => $lat, 'lng' => $lng]);
    }

    /**
     * User wanna visit an event.
     *
     * @Route(path="/addwantstovisitevent/{slug}", name="add_wants_to_visit_event",
     *     options = {"expose"=true})
     * @Security("has_role('ROLE_USER')")
     *
     * @param string  $slug
     * @param Request $request
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse|Response|NotFoundHttpException
     */
    public function userAddWantsToVisitEventAction($slug, Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $result = false;
        $html = '';
        $em = $this->getDoctrine()->getManager();
        $event = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')->findOneBy(['slug' => $slug]);

        if (!$event) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['result' => $result, 'error' => 'Unable to find Event by slug: '.$slug]);
            }
            throw $this->createNotFoundException(sprintf('Unable to find Event by slug: %s', $slug));
        }

        if ($event->isActiveAndFuture()) {
            $result = $user->addWantsToVisitEvents($event);
            $error = $result ? '' : 'cant remove event '.$slug;
        } else {
            $error = 'Event not active!';
        }

        if ($result) {
            $html = $this->get('translator')->trans('ticket.status.not_take_apart');
            $em->persist($user);
            $em->flush();
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['result' => $result, 'error' => $error, 'html' => $html]);
        }

        return $this->redirect($this->get('app.url_for_redirect')->getRedirectUrl($request->headers->get('referer')));
    }

    /**
     * User dont want to visit an event.
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
        $event = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')->findOneBy(['slug' => $slug]);

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
     * @Route(path="/event/{eventSlug}/page/venue", name="show_event_venue_page")
     *
     * @param string $eventSlug
     *
     * @Template("ApplicationDefaultBundle:Redesign:venue_review.html.twig")
     *
     * @return array
     */
    public function showEventVenuePageAction($eventSlug)
    {
        $resultArray = $this->getEventPagesArr($eventSlug);
        if (null === $resultArray['venuePage']) {
            throw $this->createNotFoundException(sprintf('Unable to find page by slug: venue'));
        }

        $newText = $resultArray['venuePage']->getTextNew();
        $text = isset($newText) && !empty($newText) ? $newText : $resultArray['venuePage']->getText();

        return array_merge($resultArray, ['text' => $text]);
    }

    /**
     * @Route(path="/event/{eventSlug}/page/{pageSlug}", name="show_event_page")
     *
     * @param string $eventSlug
     * @param string $pageSlug
     *
     * @Template("ApplicationDefaultBundle:Redesign:static.page.html.twig")
     *
     * @return array
     */
    public function showEventPageInStaticAction($eventSlug, $pageSlug)
    {
        $event = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')->findOneBy(['slug' => $eventSlug]);
        if (!$event) {
            throw $this->createNotFoundException(sprintf('Unable to find event by slug: ', $eventSlug));
        }
        /** @var ArrayCollection $pages */
        $pages = $this->getEventPages($event);
        $myPage = null;
        /** @var EventPage $page */
        foreach ($pages as $page) {
            if ($pageSlug === $page->getSlug()) {
                $myPage = $page;
                break;
            }
        }

        if (!$myPage) {
            throw $this->createNotFoundException(sprintf('Unable to find event page by slug: %s', $pageSlug));
        }
        $newText = $myPage->getTextNew();
        $text = isset($newText) && !empty($newText) ? $newText : $myPage->getText();

        return ['text' => $text];
    }

    /**
     * Get event pages that may show.
     *
     * @param Event $event
     *
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

    /**
     * Return array of event with pages.
     *
     * @param string      $eventSlug
     * @param string|null $reviewSlug
     *
     * @return array
     */
    private function getEventPagesArr($eventSlug, $reviewSlug = null)
    {
        $event = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')->findOneBy(['slug' => $eventSlug]);
        if (!$event || ($event->isAdminOnly() && !$this->isGranted('ROLE_ADMIN'))) {
            throw $this->createNotFoundException(sprintf('Unable to find event by slug: %s', $eventSlug));
        }
        $review = null;
        if ($reviewSlug) {
            $review = $this->getDoctrine()->getRepository('StfalconEventBundle:Review')->findOneBy(['slug' => $reviewSlug]);

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
            if ('program' === $page->getSlug()) {
                $programPage = $page;
                unset($pages[$key]);
            } elseif ('venue' === $page->getSlug()) {
                $venuePage = $page;
                unset($pages[$key]);
            }
        }

        $eventCurrentAmount = $this->getDoctrine()->getRepository('ApplicationDefaultBundle:TicketCost')->getEventCurrentCost($event);

        return [
            'event' => $event,
            'programPage' => $programPage,
            'venuePage' => $venuePage,
            'pages' => $pages,
            'review' => $review,
            'eventCurrentAmount' => $eventCurrentAmount,
        ];
    }
}
