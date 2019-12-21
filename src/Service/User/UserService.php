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

    public const RESULT_THROW_ON_NULL = 'throw_on_null';
    public const RESULT_RETURN_IF_NULL = 'result_return_null';

    /**
     * @param string $throw
     *
     * @return User|null
     *
     * @throws AccessDeniedException
     */
    public function getCurrentUser(string $throw = self::RESULT_THROW_ON_NULL): ?User
    {
        $user = null;

        $token = $this->tokenStorage->getToken();
        if ($token instanceof TokenInterface) {
            $user = $token->getUser();
        }

        $user = $user instanceof User ? $user : null;

        if (null === $user && self::RESULT_THROW_ON_NULL === $throw) {
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
