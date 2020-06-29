<?php

declare(strict_types=1);

namespace App\Service\Google;

use App\Entity\User;

/**
 * GoogleCalendarApi.
 */
class GoogleCalendarApi
{
    private $googleApiServiceFactory;
    private $googleCalendarId;

    /**
     * @param GoogleApiServiceFactory $googleApiServiceFactory
     * @param string                  $googleCalendarId
     */
    public function __construct(GoogleApiServiceFactory $googleApiServiceFactory, string $googleCalendarId)
    {
        $this->googleApiServiceFactory = $googleApiServiceFactory;
        $this->googleCalendarId = $googleCalendarId;
    }

    /**
     * @param \Google_Service_Calendar_Event $event
     *
     * @return \Google_Service_Calendar_Event
     */
    public function insertEvent(\Google_Service_Calendar_Event $event): \Google_Service_Calendar_Event
    {
        $calendar = $this->googleApiServiceFactory->createCalendar();
//        $events = $calendar->events->listEvents($this->googleCalendarId);

        return $calendar->events->insert($this->googleCalendarId, $event);
    }

    /**
     * @param string $eventId
     */
    public function deleteEventById(string $eventId): void
    {
        $calendar = $this->googleApiServiceFactory->createCalendar();
        $calendar->events->delete($this->googleCalendarId, $eventId);
    }

    /**
     * @param string $eventId
     *
     * @return \Google_Service_Calendar_Event|null
     */
    public function getEventById(string $eventId): ?\Google_Service_Calendar_Event
    {
        $calendar = $this->googleApiServiceFactory->createCalendar();

        return $calendar->events->get($this->googleCalendarId, $eventId);
    }

    /**
     * @param string $eventId
     * @param User   $user
     */
    public function addAttendeeToGoogleEvent(string $eventId, User $user): void
    {
        $calendar = $this->googleApiServiceFactory->createCalendar();

        $googleEvent = $calendar->events->get($this->googleCalendarId, $eventId);
        $attendees = $googleEvent->getAttendees();

        $googleAttendee = new \Google_Service_Calendar_EventAttendee();
        $googleAttendee->setDisplayName($user->getFullname());
        $googleAttendee->setEmail($user->getEmail());

        $attendees[] = $googleAttendee;
        $googleEvent->setAttendees($attendees);

        $calendar->events->update($this->googleCalendarId, $eventId, $googleEvent);
    }
}
