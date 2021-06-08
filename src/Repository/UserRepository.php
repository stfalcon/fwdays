<?php

namespace App\Repository;

use App\Entity\Mail;
use App\Entity\MailQueue;
use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class UserRepository.
 */
class UserRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Get users admin.
     *
     * @return User[]
     */
    public function getAdmins(): array
    {
        return $this->createQueryBuilder('u')
            ->where("u.roles LIKE '%_ADMIN%'")
            ->getQuery()
            ->getResult();
    }

    /**
     * @param bool $ignoreUnsubscribe
     *
     * @return mixed
     */
    public function getAllSubscribed(bool $ignoreUnsubscribe = false)
    {
        $qb = $this->createQueryBuilder('u');
        $this->addIgnoreUnsubscribeFilter($qb, $ignoreUnsubscribe);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return int
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTotalUserCount(): int
    {
        $qb = $this->getCountBaseQueryBuilder();

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return int
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getEnabledUserCount(): int
    {
        $qb = $this->getCountBaseQueryBuilder();
        $qb->where($qb->expr()->eq('u.enabled', ':enabled'))
            ->setParameter('enabled', true);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return int
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getSubscribedUserCount(): int
    {
        $qb = $this->getCountBaseQueryBuilder();
        $qb->where($qb->expr()->eq('u.subscribe', ':subscribed'))
            ->setParameter('subscribed', true);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return int
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUserHasReferalCount(): int
    {
        $qb = $this->getCountBaseQueryBuilder();
        $qb->where($qb->expr()->isNotNull('u.userReferral'));

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param bool $refused
     *
     * @return int
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getProvideDataUserCount(bool $refused): int
    {
        $qb = $this->getCountBaseQueryBuilder();
        $qb->where($qb->expr()->eq('u.allowShareContacts', ':allowShareContacts'))
            ->setParameter('allowShareContacts', $refused);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string $locale
     *
     * @return int
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUserCountByEmailLanguage(string $locale): int
    {
        $qb = $this->getCountBaseQueryBuilder();
        $qb
            ->where($qb->expr()->eq('u.emailLanguage', ':locale'))
            ->setParameter('locale', $locale)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param bool            $isEventsRegisteredUsers
     * @param ArrayCollection $events
     * @param bool            $isIgnoreUnsubscribe
     * @param string|null     $paymentStatus
     * @param string|null     $ticketType
     *
     * @return User[]
     */
    public function getUsersForEmail(bool $isEventsRegisteredUsers, ArrayCollection $events, bool $isIgnoreUnsubscribe, ?string $paymentStatus, ?string $ticketType): array
    {
        $qb = $this->createQueryBuilder('u');

        if ($isEventsRegisteredUsers) {
            $this->addRegisteredEventsFilter($qb, $events);
        }

        $this->addPaymentStatusFilter($qb, $events, $paymentStatus, $ticketType);

        $qb->groupBy('u');

        $this->addIgnoreUnsubscribeFilter($qb, $isIgnoreUnsubscribe);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Mail $mail
     *
     * @return array|null
     */
    public function getUsersFromMail($mail)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->join(MailQueue::class, 'mq')
            ->where($qb->expr()->eq('mq.mail', ':mail'))
            ->andWhere($qb->expr()->eq('mq.user', 'u'))
            ->setParameter('mail', $mail)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param \DateTime $since
     * @param \DateTime $till
     *
     * @return array
     */
    public function findUnsubscribedCountForEveryDate(\DateTime $since, \DateTime $till): array
    {
        $startSince = clone $since;
        $endTill = clone $till;

        $startSince->setTime(0, 0);
        $endTill->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('u');

        $qb->select('DATE(u.unsubscribedAt) as unsubscribed_at, COUNT(u.id) as user_count')
            ->where($qb->expr()->isNotNull('u.unsubscribedAt'))
            ->andWhere($qb->expr()->between('u.unsubscribedAt', ':date_from', ':date_to'))
            ->setParameters(new ArrayCollection([
                new Parameter('date_from', $startSince),
                new Parameter('date_to', $endTill),
            ]))
            ->groupBy('unsubscribed_at')
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
                Ticket::class,
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

        $qb->setParameters(new ArrayCollection([
            new Parameter('check_event', $checkEventId),
            new Parameter('status', Payment::STATUS_PAID),
            new Parameter('object_id', $hasTicketObjectId),
        ]));

        return $qb->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param bool         $ignoreUnsubscribe
     */
    private function addIgnoreUnsubscribeFilter(QueryBuilder $qb, bool $ignoreUnsubscribe): void
    {
        if (!$ignoreUnsubscribe) {
            $qb->andWhere($qb->expr()->eq('u.subscribe', ':subscribe'))
                ->setParameter('subscribe', true)
            ;
        }
    }

    /**
     * @param QueryBuilder    $qb
     * @param ArrayCollection $events
     */
    private function addRegisteredEventsFilter(QueryBuilder $qb, ArrayCollection $events): void
    {
        if ($events->count() > 0) {
            $qb
                ->join('u.eventRegistrations', 'ur', Join::WITH, 'ur.user = u AND ur.event IN (:events)')
                ->setParameter('events', $events->toArray())
            ;
        }
    }

    /**
     * @param QueryBuilder    $qb
     * @param ArrayCollection $events
     */
    private function addEventsWithTicketFilter(QueryBuilder $qb, ArrayCollection $events): void
    {
        if ($events->count() > 0) {
            $onExp = 'all_tickets.user = u AND all_tickets.event IN (:events)';
            $qb
                ->join(Ticket::class, 'all_tickets', Join::WITH, $onExp)
                ->setParameter('events', $events->toArray())
            ;
        }
    }

    /**
     * @param QueryBuilder    $qb
     * @param ArrayCollection $events
     * @param string|null     $paymentStatus
     * @param string|null     $ticketType
     */
    private function addPaymentStatusFilter(QueryBuilder $qb, ArrayCollection $events, ?string $paymentStatus, ?string $ticketType): void
    {
        if (null !== $paymentStatus && $events->count() > 0) {
            $onExp = 't.user = u AND t.event IN (:events)';

            if (Payment::STATUS_PENDING === $paymentStatus) {
                $qb
                    ->leftJoin(Ticket::class, 't', Join::WITH, $onExp)
                    ->leftJoin('t.payment', 'p');

                $statusQuery = $qb->expr()->orX(
                    $qb->expr()->eq('p.status', ':status'),
                    $qb->expr()->isNull('p.status'), //has not payment
                    $qb->expr()->isNull('t.user') //has not ticket
                );
            } else {
                $qb
                    ->join(Ticket::class, 't', Join::WITH, $onExp)
                    ->join('t.payment', 'p')
                ;

                $statusQuery = $qb->expr()->eq('p.status', ':status');
            }
            if (null !== $ticketType) {
                $qb
                    ->join('t.ticketCost', 'tc')
                    ->setParameter('ticket_type', $ticketType)
                ;
                $statusQuery = $qb->expr()->andX(
                    $statusQuery,
                    $qb->expr()->eq('tc.type', ':ticket_type')
                );
            }

            $qb->andWhere($qb->expr()->andX($statusQuery))
                ->setParameter('events', $events->toArray())
                ->setParameter('status', $paymentStatus)
            ;
        }
    }

    /**
     * @return QueryBuilder
     */
    private function getCountBaseQueryBuilder()
    {
        return $this->createQueryBuilder('u')->select('COUNT(u)');
    }
}
