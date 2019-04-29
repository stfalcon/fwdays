<?php

namespace Application\Bundle\DefaultBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Bundle\DefaultBundle\Entity\Event;

/**
 * Class TicketCostRepository.
 */
class TicketCostRepository extends EntityRepository
{
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
        $qb->andWhere($qb->expr()->eq('tc.enabled', 1));

        $result = $qb->getQuery()->getResult();
        $result = is_array($result) ? array_shift($result) : null;

        $currentCost = $result ? $result['amount'] : null;

        return $currentCost;
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
        $qb->andWhere($qb->expr()->eq('tc.enabled', 1));

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
            ->setParameter(':event', $event)
            ->orderBy('tc.amount');

        return $qb;
    }
}
