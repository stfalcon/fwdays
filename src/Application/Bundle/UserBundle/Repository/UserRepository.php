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
}