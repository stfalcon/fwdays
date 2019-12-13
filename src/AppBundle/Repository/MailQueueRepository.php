<?php

namespace App\Repository;

use App\Entity\Mail;
use App\Entity\MailQueue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Parameter;

/**
 * MailQueueRepository.
 */
class MailQueueRepository extends EntityRepository
{
    /**
     * @param int $limit
     *
     * @return MailQueue[]
     */
    public function getMessages($limit): array
    {
        $qb = $this->createQueryBuilder('mq');
        $qb
             ->join('mq.mail', 'm')
             ->where($qb->expr()->eq('m.start', ':start'))
             ->andWhere($qb->expr()->eq('mq.isSent', ':sent'))
             ->setParameters(
                 new ArrayCollection([
                         new Parameter('start', true),
                         new Parameter('sent', false),
                 ])
             )
             ->setMaxResults($limit)
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
