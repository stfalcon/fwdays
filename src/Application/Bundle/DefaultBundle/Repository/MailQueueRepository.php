<?php

namespace Application\Bundle\DefaultBundle\Repository;

use Application\Bundle\DefaultBundle\Entity\Mail;
use Application\Bundle\DefaultBundle\Entity\MailQueue;
use Doctrine\ORM\EntityRepository;

/**
 * Class MailQueueRepository.
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

    /**
     * @param bool $sent
     *
     * @return array
     */
    public function getAllMessages($sent)
    {
        $qb = $this->createQueryBuilder('mq');
        $qb->join('mq.mail', 'm')
            ->where($qb->expr()->eq('mq.isSent', ':sent'))
            ->setParameter('sent', $sent)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Mail $mail
     *
     * @return int
     */
    public function deleteAllNotSentMessages($mail)
    {
        $qb = $this->createQueryBuilder('mq');
        $qb->delete()
            ->where($qb->expr()->eq('mq.isSent', 0))
            ->andWhere($qb->expr()->eq('mq.mail', ':mail'))
            ->setParameter('mail', $mail)
        ;

        return $qb->getQuery()->getResult();
    }
}
