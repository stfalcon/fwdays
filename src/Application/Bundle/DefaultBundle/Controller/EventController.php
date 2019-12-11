<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Entity\Review;
use Application\Bundle\DefaultBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * EventController.
 */
class EventController extends Controller
{
    /**
     * @Route("/events", name="events")
     *
     * @return Response
     */
    public function eventsAction(): Response
    {
        $activeEvents = $this->getDoctrine()->getManager()
            ->getRepository(Event::class)
            ->findBy(['active' => true], ['date' => 'ASC']);

        $pastEvents = $this->getDoctrine()->getManager()
            ->getRepository(Event::class)
            ->findBy(['active' => false], ['date' => 'DESC']);

        return $this->render('@ApplicationDefault/Redesign/Event/events.html.twig', [
            'activeEvents' => $activeEvents,
            'pastEvents' => $pastEvents,
        ]);
    }

    /**
     * Show event.
     *
     * @Route("/event/{slug}", name="event_show")
     *
     * @param Event $event
     *
     * @return Response
     */
    public function showAction(Event $event): Response
    {
        $referralService = $this->get('app.referral.service');
        $referralService->handleRequest($this->container->get('request_stack')->getCurrentRequest());

        return $this->render('@ApplicationDefault/Redesign/Event/event.html.twig', $this->get('app.event.service')->getEventPages($event));
    }

    /**
     * @Route("/event/{slug}/review/{reviewSlug}", name="event_review_show")
     *
     * @ParamConverter("review", class="ApplicationDefaultBundle:Review", options={"slug" = "reviewSlug"})
     *
     * @param Event  $event
     * @param Review $review
     *
     * @return Response
     */
    public function showEventReviewAction(Event $event, Review $review): Response
    {
        $pages = $this->get('app.event.service')->getEventPages($event, $review);

        return $this->render('ApplicationDefaultBundle:Redesign/Speaker:report_review.html.twig', $pages);
    }

    /**
     * Get event map position.
     *
     * @Route(path="/get_map_pos/{slug}", name="get_event_map_position",
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     *
     * @param Event $event
     *
     * @return JsonResponse
     */
    public function getEventMapPosition(Event $event): JsonResponse
    {
        if ($this->get('app.service.google_map_service')->setEventMapPosition($event)) {
            $this->getDoctrine()->getManager()->flush($event);

            return new JsonResponse(['result' => true, 'lat' => $event->getLat(), 'lng' => $event->getLng()]);
        }

        return new JsonResponse(['result' => false]);
    }

    /**
     * @Route(path="/addwantstovisitevent/{slug}", name="add_wants_to_visit_event",
     *     options = {"expose"=true})
     * @Security("has_role('ROLE_USER')")
     *
     * @param Event   $event
     * @param Request $request
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse|Response|NotFoundHttpException
     */
    public function userAddWantsToVisitEventAction(Event $event, Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $result = false;
        $html = '';
        $flashContent = '';
        $em = $this->getDoctrine()->getManager();

        if ($event->isActiveAndFuture()) {
            $result = $user->addWantsToVisitEvents($event);
            $error = $result ? '' : \sprintf('cant remove event %s', $event->getSlug());
        } else {
            $error = 'Event not active!';
        }

        if ($result) {
            $translator = $this->get('translator');
            $flashContent = $translator->trans('flash_you_registrated.title');
            $html = $translator->trans('ticket.status.not_take_apart');
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
     * @param Event $event
     *
     * @return JsonResponse
     */
    public function userSubWantsToVisitEventAction(Event $event)
    {
        /** @var User $user */
        $user = $this->getUser();
        $result = false;
        $html = '';
        $flashContent = '';
        $em = $this->getDoctrine()->getManager();

        if ($event->isActiveAndFuture()) {
            $result = $user->subtractWantsToVisitEvents($event);
            $error = $result ? '' : \sprintf('cant remove event %s', $event->getSlug());
        } else {
            $error = 'Event not active!';
        }

        if ($result) {
            $translator = $this->get('translator');
            $flashContent = $translator->trans('flash_you_unsubscribe.title');
            $html = $translator->trans('ticket.status.take_apart');
            $em->flush();
        }

        return new JsonResponse(['result' => $result, 'error' => $error, 'html' => $html, 'flash' => $flashContent]);
    }

    /**
     * @Route(path="/event/{slug}/page/venue", name="show_event_venue_page")
     *
     * @param Event $event
     *
     * @return Response
     */
    public function showEventVenuePageAction(Event $event): Response
    {
        $resultArray = $this->get('app.event.service')->getEventPages($event);
        if (null === $resultArray['venuePage']) {
            throw $this->createNotFoundException(sprintf('Unable to find page by slug: venue'));
        }

        $newText = $resultArray['venuePage']->getTextNew();
        $text = isset($newText) && !empty($newText) ? $newText : $resultArray['venuePage']->getText();

        return $this->render('ApplicationDefaultBundle:Redesign:venue_review.html.twig', \array_merge($resultArray, ['text' => $text]));
    }
}
