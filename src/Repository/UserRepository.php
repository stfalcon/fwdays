<?php

namespace App\Repository;

use App\Entity\Mail;
use App\Entity\MailQueue;
use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Andx;
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
     * Users registered for events.
     *
     * @param ArrayCollection $events
     * @param bool            $ignoreUnsubscribe
     * @param string|null     $status
     *
     * @return array
     */
    public function getRegisteredUsers(ArrayCollection $events, bool $ignoreUnsubscribe = false, ?string $status = null)
    {
        $qb = $this->createQueryBuilder('u');
        $andX = $qb->expr()->andX();

        if ($events->count() > 0) {
            $this->addEventsFilter($qb, $andX, $events);
            $this->addPaymentStatusFilter($qb, $andX, $status);
        }

        $qb->andWhere($andX)
            ->groupBy('u')
        ;

        $this->addIgnoreUnsubscribeFilter($qb, $ignoreUnsubscribe);
        $users = $qb->getQuery()->getResult();

        return $users;
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
     * @param Andx            $andX
     * @param ArrayCollection $events
     */
    private function addEventsFilter(QueryBuilder $qb, Andx $andX, ArrayCollection $events): void
    {
        $qb->join('u.eventRegistrations', 'er');
        $andX->add($qb->expr()->in('er.event', ':events'));
        $qb->setParameter(':events', $events->toArray());
    }

    /**
     * @param QueryBuilder $qb
     * @param Andx         $andX
     * @param string|null  $status
     */
    private function addPaymentStatusFilter(QueryBuilder $qb, Andx $andX, ?string $status = null): void
    {
        if (null !== $status) {
            $onExp = 't.user = u AND t.event = :events';

            if (Payment::STATUS_PENDING === $status) {
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

            $andX->add($statusQuery);
            $qb->setParameter(':status', $status);
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
