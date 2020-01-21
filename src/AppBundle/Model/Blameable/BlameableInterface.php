<?php

declare(strict_types=1);

namespace App\Model\Blameable;

use App\Entity\User;

/**
 * BlameableInterface.
 */
interface BlameableInterface
{
    /**
     * @return User|null
     */
    public function getCreatedBy(): ?User;

    /**
     * @param User|null $admin
     */
    public function setCreatedBy(User $admin = null);
}
