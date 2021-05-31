<?php

declare(strict_types=1);

namespace App\Service\SonataBlockAccess;

use App\Entity\EventBlock;
use App\Entity\User;
use App\Service\User\UserService;
use App\Traits\UserServiceTrait;

/**
 * GrandAccessSonataBlockService.
 */
class GrandAccessSonataBlockService
{
    use UserServiceTrait;

    /** @var GrandAccessForSonataBlockInterface[]|iterable */
    private $accessProcessors;

    /**
     * @param iterable|GrandAccessForSonataBlockInterface[] $accessProcessors
     */
    public function __construct(iterable $accessProcessors)
    {
        $this->accessProcessors = $accessProcessors;
    }

    /**
     * @param EventBlock $eventBlock
     *
     * @return bool
     */
    public function isAccessGrand(EventBlock $eventBlock): bool
    {
        $user = $this->userService->getCurrentUser(UserService::RESULT_RETURN_IF_NULL);

        if ($user instanceof User && ($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_SUPER_ADMIN'))) {
            return true;
        }

        foreach ($this->accessProcessors as $accessProcessor) {
            if ($accessProcessor->support($eventBlock)) {
                return $accessProcessor->access($user, $eventBlock);
            }
        }

        return false;
    }
}
