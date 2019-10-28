<?php

declare(strict_types=1);

namespace Application\Bundle\DefaultBundle\Service;

use Application\Bundle\DefaultBundle\Entity\User;

/**
 * EmailHashValidationService.
 */
class EmailHashValidationService
{
    /**
     * @param User     $user
     * @param int|null $mailId
     *
     * @return string
     */
    public function generateHash(User $user, ?int $mailId = null): string
    {
        return \md5($user->getSalt().$mailId);
    }

    /**
     * @param string   $hash
     * @param User     $user
     * @param int|null $mailId
     *
     * @return bool
     */
    public function isHashValid(string $hash, User $user, ?int $mailId = null): bool
    {
        $correctHash = $this->generateHash($user, $mailId);

        return $correctHash === $hash;
    }
}
