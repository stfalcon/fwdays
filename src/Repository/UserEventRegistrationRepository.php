<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\UserEventRegistration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * UserEventRegistrationRepository.
 */
class UserEventRegistrationRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserEventRegistration::class);
    }

    /**
     * @param Event $event
     *
     * @return int
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getRegistrationCountByEvent(Event $event): int
    {
        $qb = $this->createQueryBuilder('ur');
        $qb->select($qb->expr()->count('ur.id'))
            ->where($qb->expr()->eq('ur.event', ':event'))
            ->setParameter('event', $event)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return bool
     */
    public function isUserRegisteredForEvent(User $user, Event $event): bool
    {
        $qb = $this->createQueryBuilder('ur');
        $qb->where($qb->expr()->eq('ur.user', ':user'))
            ->andWhere($qb->expr()->eq('ur.event', ':event'))
            ->setParameters(
                new ArrayCollection(
                    [
                        new Parameter('user', $user),
                        new Parameter('event', $event),
                    ]
                )
            )
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult() instanceof UserEventRegistration;
    }
}
