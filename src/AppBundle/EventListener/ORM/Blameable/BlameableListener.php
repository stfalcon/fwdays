<?php

declare(strict_types=1);

namespace App\EventListener\ORM\Blameable;

use App\Entity\User;
use App\Model\Blameable\BlameableInterface;
use App\Traits\TokenStorageTrait;

/**
 * BlameableListener.
 */
final class BlameableListener
{
    use TokenStorageTrait;

    /**
     * @param BlameableInterface $blameable
     */
    public function prePersist(BlameableInterface $blameable): void
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            return;
        }

        $user = $token->getUser();

        if ($user instanceof User) {
            $blameable->setCreatedBy($user);
        }
    }
}
