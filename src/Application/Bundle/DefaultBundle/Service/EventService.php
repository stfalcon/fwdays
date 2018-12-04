<?php

namespace Application\Bundle\DefaultBundle\Service;

use Application\Bundle\DefaultBundle\Repository\TicketCostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectRepository;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\EventPage;
use Stfalcon\Bundle\EventBundle\Repository\EventRepository;
use Stfalcon\Bundle\EventBundle\Repository\ReviewRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class EventService.
 */
class EventService
{
    /** @var EventRepository */
    private $eventRepository;

    /** @var TicketCostRepository */
    private $ticketCostRepository;

    /** @var ReviewRepository */
    private $reviewRepository;

    /** @var AuthorizationChecker */
    private $authorizationChecker;

    /**
     * EventService constructor.
     *
     * @param ObjectRepository              $eventRepository
     * @param ObjectRepository              $ticketCostRepository
     * @param ObjectRepository              $reviewRepository
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(ObjectRepository $eventRepository, ObjectRepository $ticketCostRepository, ObjectRepository $reviewRepository, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->eventRepository = $eventRepository;
        $this->ticketCostRepository = $ticketCostRepository;
        $this->reviewRepository = $reviewRepository;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param Event $event
     * @param null  $reviewSlug
     *
     * @return array
     */
    public function getEventPages(Event $event, $reviewSlug = null)
    {
        if ($event->isAdminOnly() && !$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            throw new NotFoundHttpException(sprintf('Unable to find event by slug: %s', $event->getSlug()));
        }
        $review = null;
        if ($reviewSlug) {
            $review = $this->reviewRepository->findOneBy(['slug' => $reviewSlug]);

            if (!$review) {
                throw new NotFoundHttpException('Unable to find Review entity.');
            }
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

        return [
            'event' => $event,
            'programPage' => $programPage,
            'venuePage' => $venuePage,
            'pages' => $pages,
            'review' => $review,
            'eventCurrentAmount' => $eventCurrentAmount,
        ];
    }

    /**
     * Return array of event with pages.
     *
     * @param string      $eventSlug
     * @param string|null $reviewSlug
     *
     * @return array
     */
    public function getEventPagesArr($eventSlug, $reviewSlug = null)
    {
        /** @var Event $event */
        $event = $this->eventRepository->findOneBy(['slug' => $eventSlug]);
        if (!$event instanceof Event) {
            throw new NotFoundHttpException(sprintf('Unable to find event by slug: ', $eventSlug));
        }

        return $this->getEventPages($event, $reviewSlug);
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
