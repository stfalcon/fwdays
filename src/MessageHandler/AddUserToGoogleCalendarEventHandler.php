<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Event;
use App\Entity\User;
use App\Message\AddUserToGoogleCalendarEventMessage;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Service\GoogleEvent\GoogleEventService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * AddUserToGoogleCalendarEventHandler
 */
class AddUserToGoogleCalendarEventHandler implements MessageHandlerInterface
{
    /** @var GoogleEventService */
    private $eventService;

    /** @var EventRepository  */
    private $eventRepository;

    /** @var UserRepository  */
    private $userRepository;

    /**
     * @param GoogleEventService $eventService
     * @param EventRepository    $eventRepository
     * @param UserRepository     $userRepository
     */
    public function __construct(GoogleEventService $eventService, EventRepository $eventRepository, UserRepository $userRepository)
    {
        $this->eventService = $eventService;
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param AddUserToGoogleCalendarEventMessage $addUserToGoogleCalendarEventMessage
     */
    public function __invoke(AddUserToGoogleCalendarEventMessage $addUserToGoogleCalendarEventMessage)
    {
        $event = $this->eventRepository->find($addUserToGoogleCalendarEventMessage->getEventId());
        $user = $this->userRepository->find($addUserToGoogleCalendarEventMessage->getUserId());

        if ($event instanceof Event && $user instanceof User && \is_string($event->getGoogleCalendarEventId())) {
            $this->eventService->addAttendeeToEvent($event, $user);
        }
    }
}
