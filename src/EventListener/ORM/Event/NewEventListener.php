<?php

declare(strict_types=1);

namespace App\EventListener\ORM\Event;

use App\Entity\Event;
use App\Message\CreateEventMessage;
use App\Service\GoogleEvent\GoogleEventService;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * NewEventListener
 */
class NewEventListener
{
    /** @var MessageBusInterface */
    private $bus;

    //@todo remove GoogleEventService after add redis and active messenger
    /** @var GoogleEventService */
    private $eventService;

    /**
     * @param MessageBusInterface $bus
     * @param GoogleEventService  $eventService
     */
    public function __construct(MessageBusInterface $bus, GoogleEventService $eventService)
    {
        $this->bus = $bus;
        $this->eventService = $eventService;
    }

    /**
     * @param Event              $event
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(Event $event, LifecycleEventArgs $args): void
    {
        if (null === $event->getGoogleCalendarEventId() && $event->isActiveAndFuture()) {
            $this->eventService->createGoogleEvent($event);
//            $this->bus->dispatch(new CreateEventMessage($event->getId()));
        }
    }

    /**
     * @param Event $event
     */
    public function postPersist(Event $event): void
    {
        if ($event->isActiveAndFuture()) {
            $this->eventService->createGoogleEvent($event);
//            $this->bus->dispatch(new CreateEventMessage($event->getId()));
        }
    }
}
