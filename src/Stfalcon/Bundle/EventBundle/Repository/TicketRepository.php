<?php

namespace Stfalcon\Bundle\EventBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Bundle\UserBundle\Entity\User;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Payment;

/**
 * TicketRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TicketRepository extends EntityRepository
{
    // @todo це ппц. половина методів незрозуміло для чого. мені треба пошук квитка для юзера на івент.
    // підозрюю, що він тут є, але так сходу не вгадаєш
    // треба передивитись методи і забрати зайве, а решту нормально назвати

    /**
     * Find tickets of active events for some user.
     *
     * @param User $user
     *
     * @return array
     */
    public function findTicketsOfActiveEventsForUser(User $user)
    {
        $qb = $this->createQueryBuilder('t');

        return $qb->join('t.event', 'e')
                  ->where($qb->expr()->eq('e.active', ':active'))
                  ->andWhere($qb->expr()->eq('t.user', ':user'))
                  ->setParameters(['user' => $user, 'active' => true])
                  ->orderBy('e.date', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * @param Event $event  Event
     * @param null  $status Status
     *
     * @return array
     */
    public function findUsersByEventAndStatus(Event $event = null, $status = null)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('u', 't', 'p')
            ->from('StfalconEventBundle:Ticket', 't')
            ->join('t.user', 'u')
            ->join('t.event', 'e')
            ->join('t.payment', 'p')
            ->andWhere('e.active = 1');

        if (null !== $event) {
            $query->andWhere('t.event = :event')
                ->setParameter(':event', $event);
        }
        if (null !== $status) {
            $query->andWhere('p.status = :status')
                ->setParameter(':status', $status);
        }

        $query = $query->getQuery();

        $users = array();
        foreach ($query->execute() as $result) {
            $users[] = $result->getUser();
        }

        return $users;
    }

    /**
     * Find users by event and status.
     *
     * @param array $events Events
     * @param null  $status Status
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findUsersByEventsAndStatusQueryBuilder($events = null, $status = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('u')
            ->addSelect('t')
            ->from('StfalconEventBundle:Ticket', 't')
            ->join('t.user', 'u')
            ->join('t.event', 'e')
            //@todo hot fix необходимо сделать рассылку для предыдущих ивентов
            //->andWhere('e.active = :eventStatus')
            //->setParameter(':eventStatus', true)
            ->groupBy('u');

        if (null !== $events) {
            $qb->andWhere($qb->expr()->in('t.event', ':events'))
                ->setParameter(':events', $events->toArray());
        }
        if (null !== $status) {
            $statusOr = $qb->expr()->orX('p.status = :status');
            if ('pending' == $status) {
                $statusOr->add('p.status IS NULL');
            }
            $qb->leftJoin('t.payment', 'p')
                ->andWhere($statusOr)
                ->setParameter(':status', $status);
        }

        return $qb;
    }

    /**
     * Find users by event and status.
     *
     * @param array $events Events
     * @param null  $status Status
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findUsersSubscribedByEventsAndStatus($events = null, $status = null)
    {
        $qb = $this->findUsersByEventsAndStatusQueryBuilder($events, $status);
        $qb->andWhere('u.subscribe = 1');

        $users = [];

        foreach ($qb->getQuery()->execute() as $result) {
            $users[] = $result->getUser();
        }

        return $users;
    }

    /**
     * Find users by event and status.
     *
     * @param array $events Events
     * @param null  $status Status
     *
     * @return array
     */
    public function findUsersByEventsAndStatus($events = null, $status = null)
    {
        $users = [];

        foreach ($this->findUsersByEventsAndStatusQueryBuilder($events, $status)->getQuery()->execute() as $result) {
            $users[] = $result->getUser();
        }

        return $users;
    }

    /**
     * Find tickets by event.
     *
     * @param Event $event
     *
     * @return array
     */
    public function findTicketsByEvent(Event $event)
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT t
                FROM StfalconEventBundle:Ticket t
                JOIN t.event e
                WHERE e.active = TRUE
                    AND t.event = :event
                GROUP BY t.user
            ')
            ->setParameter('event', $event)
            ->getResult();
    }

    /**
     * Find tickets by event group by user.
     *
     * @param Event $event
     * @param int   $count
     * @param int   $offset
     *
     * @return array
     */
    public function findTicketsByEventGroupByUser(Event $event, $count = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('t');

        $qb->select('t')
            ->join('t.event', 'e')
            ->where('e.active = true')
            ->andWhere('t.event = :event')
            ->groupBy('t.user')
            ->setParameter('event', $event);

        if (isset($count) && $count > 0) {
            $qb->setMaxResults($count);
        }

        if (isset($offset) && $offset > 0) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find ticket for some user and event with not null payment.
     *
     * @param User  $user  User
     * @param Event $event Event
     *
     * @return array
     */
    public function findOneByUserAndEvent($user, $event)
    {
        $qb = $this->createQueryBuilder('t');

        return $qb->select('t')
            ->where('t.event = :event')
            ->andWhere('t.user = :user')
            ->andWhere($qb->expr()->isNotNull('t.payment'))
            ->setParameter('event', $event)
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
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
        $qb->select('COUNT(t)')
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
            ->addSelect('COUNT(t.id)')
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
            ->from('Stfalcon\Bundle\EventBundle\Entity\Ticket', 't2')
            ->join('t.payment', 'p1')
            ->join('t2.payment', 'p2')
            ->where($qb->expr()->eq('p1.status', ':status'))
            ->andWhere($qb->expr()->eq('p2.status', ':status'))
            ->andWhere($qb->expr()->eq('t.user', 't2.user'))
            ->andWhere($qb->expr()->eq('t.event', ':event1'))
            ->andWhere($qb->expr()->eq('t2.event', ':event2'))
            ->setParameters(['status' => 'paid', 'event1' => $event1Id, 'event2' => $event2Id])
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
            ->setParameter('status', 'paid')
            ->groupBy('e.id')
            ->orderBy('e.id', 'DESC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Event|null $event
     *
     * @return array
     */
    public function getBoughtTicketsCountForTheLastGroupedByDateForChart(Event $event = null)
    {
        $now = new \DateTime();
        $monthAgo = clone $now;
        $monthAgo->modify('-1 month')->setTime(0, 0);
        $periodDates = new \DatePeriod($monthAgo, new \DateInterval('P1D'), $now);

        $qb = $this->createQueryBuilder('t');
        $qb->select('MONTH(p.updatedAt) as paymentMonth, DAY(p.updatedAt) as paymentDay, COUNT(t.id) as ticketsCount')
            ->join('t.payment', 'p')
            ->where($qb->expr()->gte('p.updatedAt', ':monthAgo'))
            ->andWhere($qb->expr()->lte('p.updatedAt', ':now'))
            ->andWhere($qb->expr()->eq('p.status', ':status'))
            ->setParameters(
                [
                    'monthAgo' => $monthAgo,
                    'now' => $now,
                    'status' => Payment::STATUS_PAID,
                ]
            );

        if ($event instanceof Event) {
            $qb->andWhere($qb->expr()->eq('t.event', ':event'))
               ->setParameter('event', $event);
        }

        $results = $qb
            ->groupBy('paymentMonth')
            ->addGroupBy('paymentDay')
            ->getQuery()
            ->getResult()
        ;

        $formattedResult = [];
        foreach ($periodDates as $periodDate) {
            $date = $periodDate->format('d.m');
            $formattedResult[$date] = [$date, 0];
        }

        foreach ($results as $result) {
            $day = $result['paymentDay'] < 10 ? '0'.$result['paymentDay'] : $result['paymentDay'];
            $month = $result['paymentMonth'] < 10 ? '0'.$result['paymentMonth'] : $result['paymentMonth'];
            $date = $day.'.'.$month;

            $formattedResult[$date][1] += (int) $result['ticketsCount'];
        }

        return array_values($formattedResult);
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
                'status' => 'paid',
                'event' => $event,
            ])
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }
}
