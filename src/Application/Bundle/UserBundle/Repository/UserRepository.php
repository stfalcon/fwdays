<?php

namespace Application\Bundle\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class UserRepository
 */
class UserRepository extends EntityRepository
{
    /**
     * Get users admin
     *
     * @return array()
     */
    public function getAdmins()
    {
        return $this->createQueryBuilder('u')
            ->where("u.roles LIKE '%ROLE_SUPER_ADMIN%'")
            ->andWhere("u.roles LIKE '%ROLE_ADMIN%'")
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function getAllSubscribed()
    {
        return $this->createQueryBuilder('u')
            ->where("u.subscribe = 1")
            ->getQuery()
            ->getResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCountBaseQueryBuilder() {
        return $this->createQueryBuilder('u')
                    ->select('COUNT(u)')
        ;
    }

    /**
     * Users registered for events
     *
     * @param $events
     *
     * @return array
     */
    public function getRegisteredUsers($events)
    {
        $qb = $this->createQueryBuilder('u');

        $qb->Join('u.wantsToVisitEvents', 'wve')
            ->where($qb->expr()->in('wve.id', ':events'))
            ->setParameter(':events', $events->toArray())
            ->andWhere('u.subscribe = 1')
            ->groupBy('u');

        return $qb->getQuery()->execute();
    }
}