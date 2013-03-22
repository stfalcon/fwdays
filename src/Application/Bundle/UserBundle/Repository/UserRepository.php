<?php

namespace Application\Bundle\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class UserRepository
 *
 * @package Application\Bundle\UserBundle\Repository
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
        $qb = $this->createQueryBuilder('mq');

        $query = $qb->where("mq.roles LIKE '%ROLE_SUPER_ADMIN%'")->getQuery();

        return $query->execute();

    }

}