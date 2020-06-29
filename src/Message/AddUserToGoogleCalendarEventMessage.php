<?php


namespace App\Message;

/**
 * AddUseToGoogleEventMessage.
 */
class AddUserToGoogleCalendarEventMessage extends EventMessage
{
    /** @var int */
    private $userId;

    /**
     * @param int $eventId
     * @param int $userId
     */
    public function __construct(int $eventId, int $userId)
    {
        parent::__construct($eventId);

        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }
}
