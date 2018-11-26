<?php

namespace Application\Bundle\UserBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Stfalcon\Bundle\EventBundle\Entity\Mail;

/**
 * Class UserRepository.
 */
class UserRepository extends EntityRepository
{
    /**
     * Get users admin.
     *
     * @return array()
     */
    public function getAdmins()
    {
        return $this->createQueryBuilder('u')
            ->where("u.roles LIKE '%_ADMIN%'")
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function getAllSubscribed()
    {
        return $this->createQueryBuilder('u')
            ->where('u.subscribe = 1')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCountBaseQueryBuilder()
    {
        return $this->createQueryBuilder('u')
                    ->select('COUNT(u)')
        ;
    }

    /**
     * Users registered for events.
     *
     * @param ArrayCollection $events
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

    /**
     * @param Mail $mail
     *
     * @return array|null
     */
    public function getUsersFromMail($mail)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->join('Stfalcon\Bundle\EventBundle\Entity\MailQueue', 'mq')
            ->where($qb->expr()->eq('mq.mail', ':mail'))
            ->andWhere($qb->expr()->eq('mq.user', 'u'))
            ->setParameter('mail', $mail)
        ;

        return $qb->getQuery()->getResult();
    }
}
