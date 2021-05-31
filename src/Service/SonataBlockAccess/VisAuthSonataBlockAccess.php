<?php

declare(strict_types=1);

namespace App\Service\SonataBlockAccess;

use App\Entity\EventBlock;
use App\Entity\User;

/**
 * VisAuthSonataBlockAccess.
 */
class VisAuthSonataBlockAccess implements GrandAccessForSonataBlockInterface
{
    /** {@inheritdoc} */
    public function support(EventBlock $eventBlock): bool
    {
        return EventBlock::VISIBILITY_AUTH === $eventBlock->getVisibility();
    }

    /** {@inheritdoc} */
    public function access(?User $user, EventBlock $eventBlock): bool
    {
        return $user instanceof User;
    }
}
