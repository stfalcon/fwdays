<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Buzz\Message\RequestInterface;
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
 * Application event controller
 */
class EventController extends Controller
{
    /**
     * Show all events
     *
     * @Route("/events", name="events")
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
     * Show event
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
     * Get event costs
     *
     * @param Event $event
     * @Template("ApplicationDefaultBundle:Redesign:event_price.html.twig")
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
     * Finds and displays a event review
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
     * List of sponsors of event
     *
     * @param Event $event
     * @Template("ApplicationDefaultBundle:Redesign:partners.html.twig")
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
        foreach ($partners as $key => $partner){
            $partnerCategory = $partnerCategoryRepository->find($partner['id']);
            if ($partnerCategory) {
                $sortedPartners[$partnerCategory->isWideContainer()][$partnerCategory->getName()][] = $partner[0];
            }
        }

        return ['partners' => $sortedPartners];
    }

    /**
     * Get event header
     *
     * @Route(path="/get_modal_header/{slug}/{headerType}", name="get_modal_header",
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     * @param $slug
     * @param $headerType
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
     * Get event map position
     *
     * @Route(path="/get_map_pos/{slug}", name="get_event_map_position",
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
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

        if (isset($response['status']) && $response['status'] === 'OK') {
            $lat = isset($response['results'][0]['geometry']['location']['lat']) ? $response['results'][0]['geometry']['location']['lat'] : 0;
            $lng = isset($response['results'][0]['geometry']['location']['lng']) ? $response['results'][0]['geometry']['location']['lng'] : 0;
        } else {
            return new JsonResponse(['result' => false, 'lat'=> $lat, 'lng' => $lng]);
        }

        return new JsonResponse(['result' => true, 'lat'=> $lat, 'lng' => $lng]);
    }
    /**
     * User wanna visit an event
     *
     * @Route(path="/addwantstovisitevent/{slug}", name="add_wants_to_visit_event",
     *     options = {"expose"=true})
     * @Security("has_role('ROLE_USER')")
     * @param string $slug
     * @param Request $request
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
                return new JsonResponse(['result' => $result, 'error' => 'Unable to find Event by slug: ' . $slug]);
            } else {
                throw $this->createNotFoundException('Unable to find Event by slug: ' . $slug);
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
                : $this->generateUrl('homepage');

            return $this->redirect($url);
        }
    }

    /**
     * User dont want to visit an event
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
        $event = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')->findOneBy(['slug' => $event_slug]);
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

        $eventCurrentAmount = $this->getDoctrine()->getRepository('ApplicationDefaultBundle:TicketCost')->getEventCurrentCost($event);
        return [
            'event'       => $event,
            'programPage' => $programPage,
            'venuePage'   => $venuePage,
            'pages'       => $pages,
            'review'      => $review,
            'eventCurrentAmount' => $eventCurrentAmount,
        ];
    }
}