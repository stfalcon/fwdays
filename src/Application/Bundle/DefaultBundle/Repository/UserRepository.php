<?php

namespace Application\Bundle\DefaultBundle\Repository;

use Application\Bundle\DefaultBundle\Entity\Ticket;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Application\Bundle\DefaultBundle\Entity\Mail;
use Application\Bundle\DefaultBundle\Entity\Payment;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

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
     * @param Andx            $andX
     * @param ArrayCollection $events
     */
    private function addEventsFilter(QueryBuilder $qb, Andx $andX, ArrayCollection $events): void
    {
        $qb->join('u.wantsToVisitEvents', 'wtv');
        $andX->add($qb->expr()->in('wtv.id', ':events'));
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
}
