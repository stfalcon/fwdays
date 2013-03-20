<?php

namespace Stfalcon\Bundle\EventBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class MailQueueRepository
 * @package Stfalcon\Bundle\EventBundle\Repository
 */
class MailQueueRepository extends EntityRepository
{
    /**
     * @param $limit
     *
     * @return mixed
     */
    public function getMessages($limit)
    {
        $qb = $this->createQueryBuilder('mq');

        $query = $qb->join('mq.mail', 'm')
                    ->andWhere('m.start = 1')
                    ->andWhere('mq.isSent = 0')
                    ->setMaxResults($limit)
                    ->getQuery();

        return $query->execute();

    }
}