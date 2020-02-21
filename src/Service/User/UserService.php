<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\UserEventRegistration;
use App\Traits;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * UserService.
 */
class UserService
{
    use Traits\TokenStorageTrait;
    use Traits\EntityManagerTrait;

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

    /**
     * @param User                    $user
     * @param Event                   $event
     * @param \DateTimeInterface|null $date
     * @param bool                    $flush
     *
     * @return bool
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function registerUserToEvent(User $user, Event $event, ?\DateTimeInterface $date = null, bool $flush = true): bool
    {
        $userEventRegistration = new UserEventRegistration($user, $event, $date);

        if ($user->addUserEventRegistration($userEventRegistration)) {
            $this->em->persist($userEventRegistration);
            if ($flush) {
                $this->em->flush();
            }

            return true;
        }

        return false;
    }

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function unregisterUserFromEvent(User $user, Event $event): bool
    {
        if ($user->removeUserEventRegistration($event)) {
            $this->em->flush();

            return true;
        }

        return false;
    }
}
