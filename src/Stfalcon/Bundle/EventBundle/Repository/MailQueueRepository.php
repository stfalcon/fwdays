<?php

namespace Stfalcon\Bundle\EventBundle\Repository;

use Doctrine\ORM\EntityRepository;

class MailQueueRepository extends EntityRepository
{
    public function getMessages($limit)
    {
        $qb = $this->createQueryBuilder('mq');

        $query = $qb->join('mq.mail', 'm')
                    ->andWhere('m.start = 1')
                    ->andWhere('mq.isSent = 0')
                    ->setMaxResults($limit)
                    ->getQuery();
        //var_dump($query->execute()); exit;
        return $query->execute();

//        return $this->getEntityManager()
//           ->createQuery('SELECT mq.*,m.start FROM StfalconEventBundle:MailQueue mq INNER JOIN StfalconEventBundle:Mail m
//           WHERE m.start=1 AND mq.is_sent=0')
//           ->setMaxResults($limit)
//           ->getResult();
    }
}