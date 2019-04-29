<?php

namespace Application\Bundle\DefaultBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Application\Bundle\DefaultBundle\Entity\Mail;
use Application\Bundle\DefaultBundle\Entity\Payment;

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

        $qb->join('u.wantsToVisitEvents', 'wve')
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
        $qb->join('Application\Bundle\DefaultBundle\Entity\MailQueue', 'mq')
            ->where($qb->expr()->eq('mq.mail', ':mail'))
            ->andWhere($qb->expr()->eq('mq.user', 'u'))
            ->setParameter('mail', $mail)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int    $checkEventId
     * @param int    $hasTicketObjectId
     * @param string $checkType
     *
     * @return array
     */
    public function getUsersNotBuyTicket($checkEventId, $hasTicketObjectId, $checkType)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select(['u.fullname', 'u.email'])
            ->leftJoin('u.tickets', 't')
            ->leftJoin(
                'Application\Bundle\DefaultBundle\Entity\Ticket',
                't1',
                'WITH',
                't1.event = :check_event AND t1.user = u'
            )
            ->join('t.event', 'e')
            ->join('t.payment', 'p')
            ->leftJoin('t1.payment', 'p1')

            ->where($qb->expr()->eq('p.status', ':status'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->isNull('t1.id'),
                $qb->expr()->neq('p1.status', ':status')
            ))
            ->groupBy('u.email')
            ->orderBy('u.fullname')
        ;

        if ('event' === $checkType) {
            $qb->andWhere($qb->expr()->eq('e.id', ':object_id'));
        } elseif ('group' === $checkType) {
            $qb->andWhere($qb->expr()->eq('e.group', ':object_id'));
        }

        $qb->setParameters([
            'check_event' => $checkEventId,
            'status' => Payment::STATUS_PAID,
            'object_id' => $hasTicketObjectId,
        ]);

        return $qb->getQuery()->getResult();
    }
}