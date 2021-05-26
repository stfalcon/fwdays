<?php

declare(strict_types=1);

namespace App\Service\SonataBlockAccess;

use App\Entity\EventBlock;
use App\Entity\User;
use App\Repository\UserEventRegistrationRepository;
use App\Traits\TicketRepositoryTrait;

/**
 * VisEventRegisteredSonataBlockAccess.
 */
class VisEventRegisteredSonataBlockAccess implements GrandAccessForSonataBlockInterface
{
    use TicketRepositoryTrait;

    /** @var UserEventRegistrationRepository */
    private $userRegistrationRepository;

    /**
     * @param UserEventRegistrationRepository $userRegistrationRepository
     */
    public function __construct(UserEventRegistrationRepository $userRegistrationRepository)
    {
        $this->userRegistrationRepository = $userRegistrationRepository;
    }

    /** {@inheritdoc} */
    public function support(EventBlock $eventBlock): bool
    {
        return EventBlock::VISIBILITY_EVENT_REGISTERED === $eventBlock->getVisibility();
    }

    /** {@inheritdoc} */
    public function access(?User $user, EventBlock $eventBlock): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        $event = $eventBlock->getEvent();
        $tickets = $this->ticketRepository->getAllPaidForUserAndEvent($user, $eventBlock->getEvent());

        return 0 === \count($tickets) && $this->userRegistrationRepository->isUserRegisteredForEvent($user, $event);
    }
}
