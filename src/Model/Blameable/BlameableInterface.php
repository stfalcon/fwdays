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
     * @param User|null $createdBy
     *
     * @return self
     */
    public function setCreatedBy(User $createdBy = null);
}
