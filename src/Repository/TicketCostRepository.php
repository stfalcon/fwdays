<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\TicketCost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * TicketCostRepository.
 */
class TicketCostRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TicketCost::class);
    }

    /**
     * Get event current cost.
     *
     * @param Event $event
     *
     * @return float
     */
    public function getEventCurrentCost(Event $event)
    {
        $qb = $this->getEventTicketsCostQB($event);
        $qb->select('tc.amount');
        $qb->andWhere($qb->expr()->eq('tc.enabled', ':enabled'))
            ->setParameter('enabled', true)
        ;

        $result = $qb->getQuery()->getResult();
        $result = \is_array($result) ? \array_shift($result) : null;

        return $result ? $result['amount'] : null;
    }

    /**
     * Get Event tickets cost.
     *
     * @param Event $event
     *
     * @return array
     */
    public function getEventTicketsCost(Event $event)
    {
        $qb = $this->getEventTicketsCostQB($event);

        return $qb->getQuery()->getResult();
    }

    /**
     * Get Event enabled tickets cost.
     *
     * @param Event $event
     *
     * @return array
     */
    public function getEventEnabledTicketsCost(Event $event)
    {
        $qb = $this->getEventTicketsCostQB($event);
        $qb->andWhere($qb->expr()->eq('tc.enabled', ':enabled'))
            ->setParameter('enabled', true)
        ;

        return  $qb->getQuery()->getResult();
    }

    /**
     * @param Event $event
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getEventTicketsCostQB(Event $event)
    {
        $qb = $this->createQueryBuilder('tc');
        $qb->where($qb->expr()->eq('tc.event', ':event'))
            ->andWhere($qb->expr()->eq('tc.visible', ':visible'))
            ->setParameter(':event', $event)
            ->setParameter(':visible', true)
            ->orderBy('tc.amount')
        ;

        return $qb;
    }
}
