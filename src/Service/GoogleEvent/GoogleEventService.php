<?php

declare(strict_types=1);

namespace App\Service\GoogleEvent;

use App\Entity\Event;
use App\Entity\User;
use App\Service\Google\{GoogleCalendarApi, GoogleCalendarEventFactory};
use Doctrine\ORM\{OptimisticLockException, ORMException};
use App\Traits\{EntityManagerTrait, LoggerTrait};

/**
 * EventService.
 */
class GoogleEventService
{
    use EntityManagerTrait, LoggerTrait;

    private $googleCalendarApi;
    private $calendarEventFactory;

    /**
     * @param GoogleCalendarApi          $googleCalendarApi
     * @param GoogleCalendarEventFactory $calendarEventFactory
     */
    public function __construct(GoogleCalendarApi $googleCalendarApi, GoogleCalendarEventFactory $calendarEventFactory)
    {
        $this->googleCalendarApi = $googleCalendarApi;
        $this->calendarEventFactory = $calendarEventFactory;
    }

    /**
     * @param Event $event
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createGoogleEvent(Event $event): void
    {
        $googleEvent = $this->calendarEventFactory->create($event);
        $googleEvent = $this->googleCalendarApi->insertEvent($googleEvent);

        $event->setGoogleCalendarEventId($googleEvent->getId());

        $this->em->flush($event);
    }

    /**
     * @param Event $event
     * @param User  $user
     */
    public function addAttendeeToEvent(Event $event, User $user): void
    {
        $googleCalendarEventId = $event->getGoogleCalendarEventId();
        if (\is_string($googleCalendarEventId)) {
            $this->googleCalendarApi->addAttendeeToGoogleEvent($googleCalendarEventId, $user);
        }
    }
}
