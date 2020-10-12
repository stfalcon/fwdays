<?php

declare(strict_types=1);

namespace App\Service\VideoAccess;

use App\Entity\Event;
use App\Entity\Ticket;
use App\Entity\TicketCost;
use App\Entity\User;

/**
 * BoughtPremiumTicketGrandAccess.
 */
class BoughtPremiumTicketGrandAccess implements GrandAccessForVideoInterface
{
    /**
     * @param string $accessType
     *
     * @return bool
     */
    public function support(string $accessType): bool
    {
        return GrandAccessVideoService::BOUGHT_PREMIUM_TICKET === $accessType;
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
        if (!$user instanceof User) {
            return false;
        }

        /** @var Ticket $ticket */
        foreach ($tickets as $ticket) {
            if ($ticket->getEvent()->isEqualTo($event) &&
                $ticket->isPaid() &&
                $ticket->getTicketCost() instanceof TicketCost &&
                TicketCost::TYPE_PREMIUM === $ticket->getTicketCost()->getType()
            ) {
                return true;
            }
        }

        return false;
    }
}
