<?php
/**
 * Created by JetBrains PhpStorm.
 * User: zion
 * Date: 18.03.13
 * Time: 17:33
 * To change this template use File | Settings | File Templates.
 */

namespace Application\Bundle\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository {

    public function getAdmins()
    {
        $qb = $this->createQueryBuilder('mq');

        $query = $qb->where("mq.roles LIKE '%ROLE_SUPER_ADMIN%'")->getQuery();

        return $query->execute();

    }
}