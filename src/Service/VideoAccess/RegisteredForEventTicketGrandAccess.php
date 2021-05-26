<?php

declare(strict_types=1);

namespace App\Service\VideoAccess;

use App\Entity\Event;
use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\UserEventRegistrationRepository;

/**
 * RegisteredForEventTicketGrandAccess.
 */
class RegisteredForEventTicketGrandAccess implements GrandAccessForVideoInterface
{
    /** @var UserEventRegistrationRepository */
    private $userRegistrationRepository;

    /**
     * @param UserEventRegistrationRepository $userRegistrationRepository
     */
    public function __construct(UserEventRegistrationRepository $userRegistrationRepository)
    {
        $this->userRegistrationRepository = $userRegistrationRepository;
    }

    /**
     * @param string $accessType
     *
     * @return bool
     */
    public function support(string $accessType): bool
    {
        return GrandAccessVideoService::REGISTERED_FOR_EVENT_AND_HAVE_NOT_ANY_TICKET === $accessType;
    }

    /**
     * @param Event          $event
     * @param User|null      $user
     * @param array|Ticket[] $tickets
     *
     * @return bool
     */
    public function access(Event $event, ?User $user, array $tickets): bool
    {
        return true;
    }
}
