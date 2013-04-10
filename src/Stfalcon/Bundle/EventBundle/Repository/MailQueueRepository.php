<?php

namespace Stfalcon\Bundle\EventBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Stfalcon\Bundle\EventBundle\Entity\MailQueue;

/**
 * Class MailQueueRepository
 */
class MailQueueRepository extends EntityRepository
{
    /**
     * @param int $limit
     *
     * @return MailQueue[]
     */
    public function getMessages($limit)
    {
        return $this->createQueryBuilder('mq')
                ->join('mq.mail', 'm')
                ->where('m.start = 1')
                    ->andWhere('mq.isSent = 0')
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();
    }
}
