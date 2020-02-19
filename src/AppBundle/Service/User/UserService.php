<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\UserEventRegistration;
use App\Traits\TokenStorageTrait;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * UserService.
 */
class UserService
{
    use TokenStorageTrait;

    /** @var EntityManager */
    private $em;

    /** @param EntityManager $em */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

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

    /**
     * @param User           $user
     * @param Event          $event
     * @param null|\DateTime $date
     * @param bool           $flush
     *
     * @return bool
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function registerUserToEvent(User $user, Event $event, ?\DateTime $date = null, bool $flush = true): bool
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
