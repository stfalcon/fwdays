<?php

declare(strict_types=1);

namespace App\Service\SonataBlockAccess;

use App\Entity\EventBlock;
use App\Entity\User;

/**
 * GrandAccessForSonataBlockInterface.
 */
interface GrandAccessForSonataBlockInterface
{
    /**
     * @param EventBlock $eventBlock
     *
     * @return bool
     */
    public function support(EventBlock $eventBlock): bool;

    /**
     * @param User|null  $user
     * @param EventBlock $eventBlock
     *
     * @return bool
     */
    public function access(?User $user, EventBlock $eventBlock): bool;
}
