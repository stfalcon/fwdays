<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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

        return $this->get('app.event.service')->getEventPagesArr($eventSlug);
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
        return $this->get('app.event.service')->getEventPagesArr($eventSlug, $reviewSlug);
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

        if ($this->get('app.service.google_map_service')->setEventMapPosition($event)) {
            $this->getDoctrine()->getManager()->flush($event);

            return new JsonResponse(['result' => true, 'lat' => $event->getLat(), 'lng' => $event->getLng()]);
        }

        return new JsonResponse(['result' => false]);
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
        $flashContent = '';
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
            $flashContent = $this->get('translator')->trans('flash_you_registrated.title');
            $html = $this->get('translator')->trans('ticket.status.not_take_apart');
            $em->persist($user);
            $em->flush();
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['result' => $result, 'error' => $error, 'html' => $html, 'flash' => $flashContent]);
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
        $flashContent = '';
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
            $flashContent = $this->get('translator')->trans('flash_you_unsubscribe.title');
            $html = $this->get('translator')->trans('ticket.status.take_apart');
            $em->flush();
        }

        return new JsonResponse(['result' => $result, 'error' => $error, 'html' => $html, 'flash' => $flashContent]);
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
        $resultArray = $this->get('app.event.service')->getEventPagesArr($eventSlug);
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
        $pages = $this->get('app.event.service')->getEventMenuPages($event);
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
}
