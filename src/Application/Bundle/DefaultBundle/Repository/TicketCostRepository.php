<?php

namespace Application\Bundle\DefaultBundle\Repository;

use Application\Bundle\DefaultBundle\Entity\TicketCost;
use Doctrine\ORM\EntityRepository;
use Stfalcon\Bundle\EventBundle\Entity\Event;

class TicketCostRepository extends EntityRepository
{
    /**
     * Get event current cost
     *
     * @param Event $event
     * @return float
     */
    public function getEventCurrentCost(Event $event)
    {
        $qb = $this->createQueryBuilder('tc');
        $qb->select('tc.amount')
            ->where('tc.event = :event')
            ->andWhere('tc.enabled = 1')
            ->setParameter(':event', $event)
            ->orderBy('tc.amount');
        $query = $qb->getQuery();

        $result = $query->getResult();
        $result = is_array($result) ? array_shift($result) : null;

        $currentCost = $result ? $result['amount'] : $event->getCost();

        return $currentCost;
    }

    /**
     * Get Event tickets cost
     *
     * @param Event $event
     * @return array
     */
    public function getEventTicketsCost(Event $event)
    {
        $qb = $this->createQueryBuilder('tc');
        $qb->where('tc.event = :event')
            ->setParameter(':event', $event)
            ->orderBy('tc.amount');
        $query = $qb->getQuery();

        return $query->getResult();
    }

    /**
     * Get Event enabled tickets cost
     *
     * @param Event $event
     * @return array
     */
    public function getEventEnabledTicketsCost(Event $event)
    {
        $qb = $this->createQueryBuilder('tc');
        $qb->where('tc.event = :event')
            ->andWhere('tc.enabled = 1')
            ->setParameter(':event', $event)
            ->orderBy('tc.amount');
        $query = $qb->getQuery();

        return $query->getResult();
    }
}