<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\TicketCost;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * TicketRepository.
 */
class TicketRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    /**
     * @param Event $event
     * @param bool  $freeTickets
     *
     * @return array
     */
    public function getSoldTicketsCountForEvent(Event $event, bool $freeTickets = false): array
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('COUNT(t.id) as tickets_sold_number, SUM(t.amount) as tickets_amount')
            ->join('t.payment', 'p')
            ->where($qb->expr()->eq('t.event', ':event'))
            ->andWhere($qb->expr()->eq('p.status', ':status'))
        ;

        if ($freeTickets) {
            $qb->andWhere($qb->expr()->eq('t.amount', ':zero'));
        } else {
            $qb->andWhere($qb->expr()->gt('t.amount', ':zero'));
        }

        $qb->setParameters(
            new ArrayCollection([
                new Parameter('event', $event),
                new Parameter('status', Payment::STATUS_PAID),
                new Parameter('zero', 0),
            ])
        );

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @param \DateTime $since
     * @param \DateTime $till
     * @param Event     $event
     *
     * @return array
     */
    public function findSoldTicketsCountBetweenDatesForEvent(\DateTime $since, \DateTime $till, Event $event): array
    {
        $startSince = clone $since;
        $endTill = clone $till;

        $startSince->setTime(0, 0);
        $endTill->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('t');
        $qb->select('DATE(p.updatedAt) as date_of_sale, COUNT(t.id) as tickets_sold_number')
            ->join('t.payment', 'p')
            ->where($qb->expr()->eq('t.event', ':event'))
            ->andWhere($qb->expr()->between('p.updatedAt', ':date_from', ':date_to'))
            ->andWhere($qb->expr()->eq('p.status', ':status'))
            ->andWhere($qb->expr()->gt('p.amount', 0))
            ->setParameters(new ArrayCollection([
                new Parameter('event', $event),
                new Parameter('date_from', $startSince),
                new Parameter('date_to', $endTill),
                new Parameter('status', Payment::STATUS_PAID),
            ]))
            ->addGroupBy('date_of_sale')
        ;

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param \DateTime $since
     * @param \DateTime $till
     *
     * @return array
     */
    public function getTicketsCountByEventsPerDateBetweenDates(\DateTime $since, \DateTime $till): array
    {
        $startSince = clone $since;
        $endTill = clone $till;

        $startSince->setTime(0, 0);
        $endTill->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('t');
        $qb->select('DATE(p.updatedAt) as date_of_sale, COUNT(t.id) as tickets_sold_count, e.name')
            ->join('t.payment', 'p')
            ->join('t.event', 'e')
            ->andWhere($qb->expr()->between('p.updatedAt', ':date_from', ':date_to'))
            ->andWhere($qb->expr()->eq('p.status', ':status'))
            ->andWhere($qb->expr()->gt('p.amount', 0))
            ->setParameters(new ArrayCollection([
                new Parameter('date_from', $startSince),
                new Parameter('date_to', $endTill),
                new Parameter('status', Payment::STATUS_PAID),
            ]))
            ->addGroupBy('e.name')
            ->addGroupBy('date_of_sale')
            ->orderBy('date_of_sale')
        ;

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Event $event
     *
     * @throws \Doctrine\ORM\Query\QueryException
     *
     * @return \DateTime|null
     */
    public function getFirstDayOfTicketSales(Event $event): ?\DateTime
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('t.createdAt')
            ->where($qb->expr()->eq('t.event', ':event'))
            ->setParameter('event', $event)
            ->orderBy('t.createdAt', Criteria::ASC)
            ->setMaxResults(1)
        ;

        $date = $qb->getQuery()->getOneOrNullResult();

        return $date['createdAt'] ?? null;
    }

    /**
     * @param User  $user  User
     * @param Event $event Event
     *
     * @return Ticket|null
     */
    public function findOneByUserAndEventWithPendingPayment(User $user, Event $event): ?Ticket
    {
        $qb = $this->createQueryBuilder('t');

        return $qb
            ->leftJoin('t.payment', 'p')
            ->where($qb->expr()->eq('t.event', ':event'))
            ->andWhere($qb->expr()->eq('t.user', ':user'))
            ->andWhere($qb->expr()->eq('p.status', ':status'))
            ->setParameters(new ArrayCollection([
                new Parameter('event', $event),
                new Parameter('user', $user),
                new Parameter('status', Payment::STATUS_PENDING),
            ]))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Get all tickets for payment.
     *
     * @param Payment $payment
     *
     * @return array
     */
    public function getAllTicketsByPayment(Payment $payment)
    {
        return $this->findBy(['payment' => $payment]);
    }

    /**
     * @return array
     */
    public function getPaidTicketsCount()
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select($qb->expr()->count('t'))
            ->addSelect('u.id')
            ->join('t.payment', 'p')
            ->join('t.user', 'u')
            ->where($qb->expr()->eq('p.status', ':status'))
            ->setParameter('status', 'paid')
            ->groupBy('u.id');

        return  $qb->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function getTicketsCountByEventGroup()
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('g.name')
            ->addSelect('u.id')
            ->addSelect($qb->expr()->count('t.id'))
            ->join('t.event', 'e')
            ->join('t.payment', 'p')
            ->join('e.group', 'g')
            ->join('t.user', 'u')
            ->where($qb->expr()->eq('p.status', ':status'))
            ->setParameter('status', 'paid')
            ->groupBy('u.id')
            ->addGroupBy('g.name')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $event1Id
     * @param int $event2Id
     *
     * @return int
     */
    public function getUserVisitsEventCount($event1Id, $event2Id)
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('COUNT(t.user) AS cnt')
            ->from(Ticket::class, 't2')
            ->join('t.payment', 'p1')
            ->join('t2.payment', 'p2')
            ->where($qb->expr()->eq('p1.status', ':status'))
            ->andWhere($qb->expr()->eq('p2.status', ':status'))
            ->andWhere($qb->expr()->eq('t.user', 't2.user'))
            ->andWhere($qb->expr()->eq('t.event', ':event1'))
            ->andWhere($qb->expr()->eq('t2.event', ':event2'))
            ->setParameters(
                new ArrayCollection([
                    new Parameter('status', Payment::STATUS_PAID),
                    new Parameter('event1', $event1Id),
                    new Parameter('event2', $event2Id),
                ])
            )
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get All event with paid tickets.
     *
     * @return array
     */
    public function getEventWithTicketsCount()
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('e.id', 'e.slug', 'COUNT(t.id) AS cnt')
            ->join('t.payment', 'p')
            ->join('t.event', 'e')
            ->where($qb->expr()->eq('p.status', ':status'))
            ->setParameter('status', Payment::STATUS_PAID)
            ->groupBy('e.id')
            ->orderBy('e.id', 'DESC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Event $event
     *
     * @return int
     */
    public function getEventTicketsWithoutTicketCostCount(Event $event)
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->select('COUNT(t.id)')
            ->join('t.payment', 'p')
            ->where($qb->expr()->eq('p.status', ':status'))
            ->andWhere($qb->expr()->eq('t.event', ':event'))
            ->andWhere($qb->expr()->isNull('t.ticketCost'))
            ->setParameters([
                'status' => Payment::STATUS_PAID,
                'event' => $event,
            ])
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param TicketCost $ticketCost
     *
     * @return float
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getAmountSumByBlock(TicketCost $ticketCost): ?float
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('SUM(t.amount)')
            ->join('t.payment', 'p')
            ->where($qb->expr()->eq('t.ticketCost', ':ticket_cost'))
            ->andWhere($qb->expr()->eq('p.status', ':status'))
            ->setParameter('ticket_cost', $ticketCost)
            ->setParameter('status', Payment::STATUS_PAID)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }
}
