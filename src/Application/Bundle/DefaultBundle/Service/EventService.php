<?php

namespace Application\Bundle\DefaultBundle\Service;

use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Entity\EventGroup;
use Application\Bundle\DefaultBundle\Entity\EventPage;
use Application\Bundle\DefaultBundle\Entity\Review;
use Application\Bundle\DefaultBundle\Repository\EventRepository;
use Application\Bundle\DefaultBundle\Repository\ReviewRepository;
use Application\Bundle\DefaultBundle\Repository\TicketCostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * Class EventService.
 */
class EventService
{
    private $eventRepository;
    private $ticketCostRepository;
    private $reviewRepository;
    private $authorizationChecker;

    /**
     * @param EventRepository      $eventRepository
     * @param TicketCostRepository $ticketCostRepository
     * @param ReviewRepository     $reviewRepository
     * @param AuthorizationChecker $authorizationChecker
     */
    public function __construct(EventRepository $eventRepository, TicketCostRepository $ticketCostRepository, ReviewRepository $reviewRepository, AuthorizationChecker $authorizationChecker)
    {
        $this->eventRepository = $eventRepository;
        $this->ticketCostRepository = $ticketCostRepository;
        $this->reviewRepository = $reviewRepository;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param Event       $event
     * @param Review|null $review
     *
     * @return array
     */
    public function getEventPages(Event $event, Review $review = null)
    {
        if ($event->isAdminOnly() && !$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            throw new NotFoundHttpException(sprintf('Unable to find event by slug: %s', $event->getSlug()));
        }

        /** @var ArrayCollection $pages */
        $pages = $this->getEventMenuPages($event);

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

        $eventCurrentAmount = $this->ticketCostRepository->getEventCurrentCost($event);

        $futureEvent = !$event->isActiveAndFuture() && $event->getGroup() instanceof EventGroup ? $this->eventRepository->findFutureEventFromSameGroup($event->getGroup()) : null;

        return [
            'event' => $event,
            'programPage' => $programPage,
            'venuePage' => $venuePage,
            'pages' => $pages,
            'review' => $review,
            'eventCurrentAmount' => $eventCurrentAmount,
            'futureEvent' => $futureEvent,
        ];
    }

    /**
     * Get event pages that may show.
     *
     * @param Event $event
     *
     * @return array
     */
    public function getEventMenuPages(Event $event)
    {
        /** TODO замінити на репозіторій */
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
