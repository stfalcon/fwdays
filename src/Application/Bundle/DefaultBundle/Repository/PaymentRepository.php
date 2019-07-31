<?php

namespace Application\Bundle\DefaultBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Bundle\DefaultBundle\Entity\User;
use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Entity\Payment;

/**
 * PaymentsRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PaymentRepository extends EntityRepository
{
    /**
     * Find paid payments user.
     *
     * @param User $user
     *
     * @return array
     */
    public function findPaidPaymentsForUser(User $user)
    {
        $qb = $this->createQueryBuilder('p');
        $query = $qb->leftJoin('p.tickets', 't')
            ->leftJoin('t.event', 'e')
            ->andWhere('e.useDiscounts = :useDiscounts')
            ->andWhere('t.user = :user')
            ->andWhere('p.status = :status')
            ->setParameter('user', $user)
            ->setParameter('useDiscounts', true)
            ->setParameter('status', Payment::STATUS_PAID)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return Payment|null
     */
    public function findPaymentByUserAndEvent(User $user, Event $event)
    {
        $qb = $this->createQueryBuilder('p');
        $query = $qb->leftJoin('p.tickets', 't')
            ->where('t.event = :event')
            ->andWhere($qb->expr()->eq('p.user', ':user'))
            ->andWhere($qb->expr()->eq('p.status', ':status'))
            ->setParameter('user', $user)
            ->setParameter('event', $event)
            ->setParameter('status', Payment::STATUS_PENDING)
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}