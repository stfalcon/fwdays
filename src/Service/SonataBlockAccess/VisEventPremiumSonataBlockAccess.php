<?php

declare(strict_types=1);

namespace App\Service\SonataBlockAccess;

use App\Entity\EventBlock;
use App\Entity\TicketCost;
use App\Entity\User;
use App\Traits\TicketRepositoryTrait;

/**
 * VisEventPremiumSonataBlockAccess.
 */
class VisEventPremiumSonataBlockAccess implements GrandAccessForSonataBlockInterface
{
    use TicketRepositoryTrait;

    /** {@inheritdoc} */
    public function support(EventBlock $eventBlock): bool
    {
        return EventBlock::VISIBILITY_EVENT_PREMIUM === $eventBlock->getVisibility();
    }

    /** {@inheritdoc} */
    public function access(?User $user, EventBlock $eventBlock): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        $tickets = $this->ticketRepository->getAllPaidForUserAndEvent($user, $eventBlock->getEvent(), TicketCost::TYPE_PREMIUM);

        return \count($tickets) > 0;
    }
}
