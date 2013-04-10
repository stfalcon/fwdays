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
        return $this->createQueryBuilder('mq')
            ->where("mq.roles LIKE '%ROLE_SUPER_ADMIN%'")
            ->getQuery()
            ->getResult();
    }
}
