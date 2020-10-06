<?php

declare(strict_types=1);

namespace App\Service\VideoAccess;

use App\Entity\Event;
use App\Entity\User;

/**
 * GrandAccessVideoService.
 */
class GrandAccessVideoService
{
    public const REGISTERED_FOR_FREE_EVENT_OR_BOUGHT_ANY_TICKET = 'registered_for_free_event_or_bought_any_ticket';
    public const BOUGHT_PREMIUM_TICKET = 'bought_premium_ticket';
    public const BOUGHT_STANDARD_TICKET = 'bought_standard_ticket';

    /** @var GrandAccessForVideoInterface[]|iterable */
    private $accessProcessors;

    /**
     * @param iterable|GrandAccessForVideoInterface[] $accessProcessors
     */
    public function __construct(iterable $accessProcessors)
    {
        $this->accessProcessors = $accessProcessors;
    }

    /**
     * @param string    $grandAccessType
     * @param Event     $event
     * @param User|null $user
     * @param array     $tickets
     *
     * @return bool
     */
    public function isAccessGrand(string $grandAccessType, Event $event, ?User $user, array $tickets): bool
    {
        if ($user instanceof User && ($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_SUPER_ADMIN'))) {
            return true;
        }

        foreach ($this->accessProcessors as $accessProcessor) {
            if ($accessProcessor->support($grandAccessType)) {
                return $accessProcessor->access($event, $user, $tickets);
            }
        }

        return false;
    }
}
