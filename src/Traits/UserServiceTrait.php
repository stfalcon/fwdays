<?php

declare(strict_types=1);

namespace App\Traits;

use App\Service\User\UserService;

/**
 * UserServiceTrait.
 */
trait UserServiceTrait
{
    /** @var UserService */
    protected $userService;

    /**
     * @param UserService $userService
     *
     * @required
     */
    public function setUserService(UserService $userService): void
    {
        $this->userService = $userService;
    }
}
