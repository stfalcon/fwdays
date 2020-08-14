<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Event;
use App\Message\CreateEventMessage;
use App\Repository\EventRepository;
use App\Service\GoogleEvent\GoogleEventService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * NewEventToGoogleCalendarHandler
 */
class NewEventToGoogleCalendarHandler implements MessageHandlerInterface
{
    /** @var GoogleEventService */
    private $eventService;

    /** @var EventRepository  */
    private $eventRepository;

    /**
     * @param GoogleEventService $eventService
     * @param EventRepository    $eventRepository
     */
    public function __construct(GoogleEventService $eventService, EventRepository $eventRepository)
    {
        $this->eventService = $eventService;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param CreateEventMessage $createEventMessage
     */
    public function __invoke(CreateEventMessage $createEventMessage)
    {
        $event = $this->eventRepository->find($createEventMessage->getEventId());

        if ($event instanceof Event && $event->isActiveAndFuture() && !$event->getGoogleCalendarEventId()) {
            $this->eventService->createGoogleEvent($event);
        }
    }
}
