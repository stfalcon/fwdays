<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use App\Traits\TokenStorageTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * UserService.
 */
class UserService
{
    use TokenStorageTrait;

    /**
     * @return User
     *
     * @throws AccessDeniedException
     */
    public function getCurrentUser(): User
    {
        $user = null;

        $token = $this->tokenStorage->getToken();
        if ($token instanceof TokenInterface) {
            $user = $token->getUser();
        }

        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        return $user;
    }

    /**
     * @return bool
     */
    public function isUserAccess(): bool
    {
        $user = null;

        $token = $this->tokenStorage->getToken();
        if ($token instanceof TokenInterface) {
            $user = $token->getUser();
        }

        return $user instanceof User;
    }
}
