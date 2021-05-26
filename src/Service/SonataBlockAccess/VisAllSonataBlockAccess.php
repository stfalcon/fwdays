<?php

declare(strict_types=1);

namespace App\Service\SonataBlockAccess;

use App\Entity\EventBlock;
use App\Entity\User;

/**
 * VisAllSonataBlockAccess.
 */
class VisAllSonataBlockAccess implements GrandAccessForSonataBlockInterface
{
    /** {@inheritdoc} */
    public function support(EventBlock $eventBlock): bool
    {
        return EventBlock::VISIBILITY_ALL === $eventBlock->getVisibility();
    }

    /** {@inheritdoc} */
    public function access(?User $user, EventBlock $eventBlock): bool
    {
        return true;
    }
}
