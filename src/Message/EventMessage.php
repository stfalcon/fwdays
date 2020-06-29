<?php

declare(strict_types=1);

namespace App\Message;

/**
 * EventMessage.
 */
class EventMessage
{
    /** @var int */
    private $eventId;

    /**
     * @param int $eventId
     */
    public function __construct(int $eventId)
    {
        $this->eventId = $eventId;
    }

    /**
     * @return int
     */
    public function getEventId(): int
    {
        return $this->eventId;
    }
}
