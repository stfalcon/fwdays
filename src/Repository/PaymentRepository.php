<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Payment;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * PaymentsRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PaymentRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    /**
     * Find paid payments user.
     *
     * @param User $user
     *
     * @return array
     */
    public function findPaidPaymentsForUser(User $user): array
    {
        $qb = $this->createQueryBuilder('p');

        return $qb->leftJoin('p.tickets', 't')
            ->leftJoin('t.event', 'e')
            ->andWhere($qb->expr()->eq('e.useDiscounts', ':useDiscounts'))
            ->andWhere($qb->expr()->eq('t.user', ':user'))
            ->andWhere($qb->expr()->eq('p.status', ':status'))
            ->setParameters(new ArrayCollection(
                [
                    new Parameter('user', $user),
                    new Parameter('status', Payment::STATUS_PAID),
                    new Parameter('useDiscounts', true),
                ]
            ))
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return Payment|null
     */
    public function findPendingPaymentByUserAndEvent(User $user, Event $event): ?Payment
    {
        $qb = $this->createQueryBuilder('p');

        return $qb->leftJoin('p.tickets', 't')
            ->where($qb->expr()->eq('t.event', ':event'))
            ->andWhere($qb->expr()->eq('p.user', ':user'))
            ->andWhere($qb->expr()->eq('p.status', ':status'))
            ->setParameters(new ArrayCollection(
                [
                    new Parameter('user', $user),
                    new Parameter('status', Payment::STATUS_PENDING),
                    new Parameter('event', $event),
                ]
            ))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param User $user
     *
     * @return Payment|null
     */
    public function findPendingPaymentByUserWithoutEvent(User $user): ?Payment
    {
        $qb = $this->createQueryBuilder('p');

        return $qb->leftJoin('p.tickets', 't')
            ->where($qb->expr()->isNull('t.event'))
            ->andWhere($qb->expr()->eq('p.user', ':user'))
            ->andWhere($qb->expr()->eq('p.status', ':status'))
            ->andWhere($qb->expr()->eq('p.gate', ':gate'))
            ->setParameters(new ArrayCollection(
                [
                    new Parameter('user', $user),
                    new Parameter('status', Payment::STATUS_PENDING),
                    new Parameter('gate', Payment::UNKNOWN_GATE),
                ]
            ))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param int  $paymentId
     * @param User $user
     *
     * @return Payment|null
     */
    public function findPendingPaymentByIdForUser(int $paymentId, User $user): ?Payment
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->where($qb->expr()->eq('p.id', ':id'))
            ->andWhere($qb->expr()->eq('p.user', ':user'))
            ->andWhere($qb->expr()->eq('p.status', ':status'))
            ->setParameters(new ArrayCollection(
                [
                    new Parameter('user', $user),
                    new Parameter('status', Payment::STATUS_PENDING),
                    new Parameter('id', $paymentId),
                ]
            ))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
