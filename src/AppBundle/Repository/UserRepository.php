<?php

namespace App\Repository;

use App\Entity\Mail;
use App\Entity\MailQueue;
use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;

/**
 * Class UserRepository.
 */
class UserRepository extends EntityRepository
{
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
     * @return QueryBuilder
     */
    public function getCountBaseQueryBuilder()
    {
        return $this->createQueryBuilder('u')->select('COUNT(u)');
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
     * @param ArrayCollection $allEvents
     * @param Collection      $selectedEvents
     * @param bool            $isIgnoreUnsubscribe
     * @param string|null     $paymentStatus
     *
     * @return User[]
     */
    public function getUsersForEmail(bool $isEventsRegisteredUsers, ArrayCollection $allEvents, Collection $selectedEvents, bool $isIgnoreUnsubscribe = false, ?string $paymentStatus = null): array
    {
        $qb = $this->createQueryBuilder('u');

        if ($isEventsRegisteredUsers) {
            $this->addRegisteredEventsFilter($qb, $allEvents);
        } else {
            $this->addEventsWithTicketFilter($qb, $allEvents);
        }

        $this->addPaymentStatusFilter($qb, $selectedEvents, $paymentStatus);

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
    private function addIgnoreUnsubscribeFilter(QueryBuilder $qb, bool $ignoreUnsubscribe)
    {
        if (!$ignoreUnsubscribe) {
            $qb->andWhere($qb->expr()->eq('u.subscribe', ':subscribe'))
                ->setParameter('subscribe', true)
            ;
        }
    }

    /**
     * @param QueryBuilder    $qb
     * @param ArrayCollection $allEvents
     */
    private function addRegisteredEventsFilter(QueryBuilder $qb, ArrayCollection $allEvents): void
    {
        if ($allEvents->count() > 0) {
            $qb
                ->join('u.eventRegistrations', 'r')
                ->andWhere($qb->expr()->in('r.event', ':all_events'))
                ->setParameter('all_events', $allEvents->toArray())
            ;
        }
    }

    /**
     * @param QueryBuilder    $qb
     * @param ArrayCollection $allEvents
     */
    private function addEventsWithTicketFilter(QueryBuilder $qb, ArrayCollection $allEvents): void
    {
        if ($allEvents->count() > 0) {
            $onExp = 'all_tickets.user = u AND all_tickets.event IN (:all_events)';
            $qb->join(Ticket::class, 'all_tickets', Join::WITH, $onExp);
            $qb->setParameter('all_events', $allEvents->toArray());
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param Collection   $selectedEvents
     * @param string|null  $paymentStatus
     */
    private function addPaymentStatusFilter(QueryBuilder $qb, Collection $selectedEvents, ?string $paymentStatus = null): void
    {
        if (null !== $paymentStatus && $selectedEvents->count() > 0) {
            $onExp = 't.user = u AND t.event IN (:selected_events)';

            if (Payment::STATUS_PENDING === $paymentStatus) {
                $qb
                    ->leftJoin(Ticket::class, 't', Join::WITH, $onExp)
                    ->leftJoin('t.payment', 'p');

                $statusQuery = $qb->expr()->orX(
                    $qb->expr()->eq('p.status', ':status'),
                    $qb->expr()->isNull('p.status'),
                    $qb->expr()->isNull('t.user')
                );
            } else {
                $qb
                    ->join(Ticket::class, 't', Join::WITH, $onExp)
                    ->join('t.payment', 'p')
                ;

                $statusQuery = $qb->expr()->eq('p.status', ':status');
            }

            $qb->andWhere($qb->expr()->andX($statusQuery))
                ->setParameter('selected_events', $selectedEvents->toArray())
                ->setParameter('status', $paymentStatus)
            ;
        }
    }
}
