<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Review;
use App\Entity\User;
use App\Service\EventService;
use App\Service\GoogleMapService;
use App\Service\ReferralService;
use App\Service\UrlForRedirect;
use App\Service\User\UserService;
use App\Traits\TranslatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * EventController.
 */
class EventController extends AbstractController
{
    use TranslatorTrait;

    private $urlForRedirect;
    private $referralService;
    private $eventService;
    private $googleMapService;
    private $userService;

    /**
     * @param UrlForRedirect   $urlForRedirect
     * @param ReferralService  $referralService
     * @param EventService     $eventService
     * @param GoogleMapService $googleMapService
     * @param UserService      $userService
     */
    public function __construct(UrlForRedirect $urlForRedirect, ReferralService $referralService, EventService $eventService, GoogleMapService $googleMapService, UserService $userService)
    {
        $this->urlForRedirect = $urlForRedirect;
        $this->referralService = $referralService;
        $this->eventService = $eventService;
        $this->googleMapService = $googleMapService;
        $this->userService = $userService;
    }

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

        return $this->render('Redesign/Event/events.html.twig', [
            'activeEvents' => $activeEvents,
            'pastEvents' => $pastEvents,
        ]);
    }

    /**
     * Show event.
     *
     * @Route("/event/{slug}", name="event_show")
     *
     * @param Event   $event
     * @param Request $request
     *
     * @return Response
     */
    public function showAction(Event $event, Request $request): Response
    {
        $this->referralService->handleRequest($request);

        return $this->render('Redesign/Event/event.html.twig', $this->eventService->getEventPages($event));
    }

    /**
     * @Route("/event/{slug}/review/{reviewSlug}", name="event_review_show")
     *
     * @ParamConverter("review", class="App\Entity\Review", options={"mapping": {"reviewSlug": "slug"}})
     *
     * @param Event  $event
     * @param Review $review
     *
     * @return Response
     */
    public function showEventReviewAction(Event $event, Review $review): Response
    {
        $pages = $this->eventService->getEventPages($event, $review);

        return $this->render('Redesign/Speaker/report_review.html.twig', $pages);
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
        if ($this->googleMapService->setEventMapPosition($event)) {
            $this->getDoctrine()->getManager()->flush();

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

        if ($event->isActiveAndFuture() && $event->isRegistrationOpen()) {
            $result = $this->userService->registerUserToEvent($user, $event);
            if ($result) {
                $this->userService->sendRegistrationEmail($user, $event);
                $flashContent = $this->translator->trans('flash_you_registrated.title');
                $html = $this->translator->trans('ticket.status.not_take_apart');
            }
            $error = $result ? '' : \sprintf('cant add event %s', $event->getSlug());
        } else {
            $error = 'Event not active!';
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['result' => $result, 'error' => $error, 'html' => $html, 'flash' => $flashContent]);
        }

        if ($result) {
            $this->addFlash('app_user_event', 'flash_you_registrated.title');
        }

        return $this->redirect($this->urlForRedirect->getRedirectUrl($request->headers->get('referer')));
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

        if ($event->isActiveAndFuture() && $event->isRegistrationOpen()) {
            $result = $this->userService->unregisterUserFromEvent($user, $event);
            $error = $result ? '' : \sprintf('cant remove event %s', $event->getSlug());
        } else {
            $error = 'Event not active!';
        }

        if ($result) {
            $flashContent = $this->translator->trans('flash_you_unsubscribe.title');
            $html = $this->translator->trans('ticket.status.take_apart');
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
        $resultArray = $this->eventService->getEventPages($event);
        if (null === $resultArray['venuePage']) {
            throw $this->createNotFoundException(sprintf('Unable to find page by slug: venue'));
        }

        $newText = $resultArray['venuePage']->getTextNew();
        $text = isset($newText) && !empty($newText) ? $newText : $resultArray['venuePage']->getText();

        return $this->render('Redesign/venue_review.html.twig', \array_merge($resultArray, ['text' => $text]));
    }
}
