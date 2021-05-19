<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * UserWithDateActionInterface.
 */
interface UserWithDateActionInterface
{
    /**
     * @return User|null
     */
    public function getUser(): ?User;

    /**
     * @return \DateTimeInterface
     */
    public function getActionDate(): \DateTimeInterface;
}
