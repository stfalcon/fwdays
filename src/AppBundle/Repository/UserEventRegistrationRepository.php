<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\ORM\EntityRepository;

/**
 * UserEventRegistrationRepository.
 */
class UserEventRegistrationRepository extends EntityRepository
{
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
}
