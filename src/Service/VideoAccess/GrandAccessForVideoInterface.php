<?php

declare(strict_types=1);

namespace App\Service\VideoAccess;

use App\Entity\Event;
use App\Entity\Ticket;
use App\Entity\User;

/**
 * GrandAccessForVideoInterface.
 */
interface GrandAccessForVideoInterface
{
    /**
     * @param string $accessType
     *
     * @return bool
     */
    public function support(string $accessType): bool;

    /**
     * @param Event          $event
     * @param User|null      $user
     * @param array|Ticket[] $tickets
     *
     * @return bool
     */
    public function access(Event $event, ?User $user, array $tickets): bool;
}
