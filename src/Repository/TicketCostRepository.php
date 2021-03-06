<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\TicketCost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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
     * @param Event       $event
     * @param string|null $type
     *
     * @return float|null
     */
    public function getEventCurrentCost(Event $event, string $type = null): ?float
    {
        $qb = $this->getEventTicketsCostQB($event);
        $qb->select('tc.amount');
        $this->enabledFilter($qb);

        if (null === $type) {
            $qb->andWhere($qb->expr()->isNull('tc.type'));
        } else {
            $qb->andWhere($qb->expr()->eq('tc.type', ':type'))
                ->setParameter('type', $type)
            ;
        }

        $result = $qb->getQuery()->getResult();
        $result = \is_array($result) ? \array_shift($result) : null;

        return $result ? (float) $result['amount'] : null;
    }

    /**
     * @param Event $event
     *
     * @return array
     */
    public function getEventLowestCost(Event $event): array
    {
        $qb = $this->getEventTicketsCostQB($event);
        $this->enabledFilter($qb);

        /** @var TicketCost[] $ticketCosts */
        $ticketCosts = $qb->getQuery()->getResult();

        $result = null;
        foreach ($ticketCosts as $ticketCost) {
            if ($ticketCost->getAmount() < $result || null === $result) {
                $result = $ticketCost->getAmount();
            }
        }

        return [$result, \count($ticketCosts)];
    }

    /**
     * @param Event $event
     *
     * @return TicketCost[]
     */
    public function getAllTicketsCostForEvent(Event $event): array
    {
        $qb = $this->getEventTicketsCostQB($event);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Event       $event
     * @param string|null $type
     *
     * @return TicketCost[]
     */
    public function getEventEnabledTicketsCost(Event $event, ?string $type): array
    {
        $qb = $this->getEventTicketsCostQB($event);
        $this->enabledFilter($qb);

        if (null === $type) {
            $qb->andWhere($qb->expr()->isNull('tc.type'));
        } else {
            $qb->andWhere($qb->expr()->eq('tc.type', ':type'))
                ->setParameter('type', $type)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Event $event
     *
     * @return TicketCost[]
     */
    public function getEventAllEnabledTicketsCost(Event $event): array
    {
        $qb = $this->getEventTicketsCostQB($event);
        $this->enabledFilter($qb);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param \DateTimeInterface $dateTime
     *
     * @return TicketCost[]
     */
    public function getEnabledTicketCostWithEndDateLessThanDate(\DateTimeInterface $dateTime): array
    {
        $qb = $this->getEnabledTicketCostWithEndDateLessThanDateQb($dateTime);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param \DateTimeInterface $dateTime
     *
     * @return TicketCost[]
     */
    public function getNotRunOutEnabledTicketCostWithEndDateLessThanDate(\DateTimeInterface $dateTime): array
    {
        $qb = $this->getEnabledTicketCostWithEndDateLessThanDateQb($dateTime);
        $qb->andWhere($qb->expr()->eq('tc.ticketsRunOut', ':run_out'))
            ->setParameter('run_out', false);

        return  $qb->getQuery()->getResult();
    }

    /**
     * @param \DateTimeInterface $dateTime
     *
     * @return QueryBuilder
     */
    private function getEnabledTicketCostWithEndDateLessThanDateQb(\DateTimeInterface $dateTime): QueryBuilder
    {
        $qb = $this->createQueryBuilder('tc');
        $qb->andWhere($qb->expr()->isNotNull('tc.endDate'))
            ->andWhere($qb->expr()->lt('tc.endDate', ':date_time'))
            ->setParameter('date_time', $dateTime)
        ;

        $this->enabledFilter($qb);

        return  $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param bool         $enabled
     */
    private function enabledFilter(QueryBuilder $qb, bool $enabled = true): void
    {
        $qb->andWhere($qb->expr()->eq('tc.enabled', ':enabled'))
            ->setParameter('enabled', $enabled)
        ;
    }

    /**
     * @param Event $event
     *
     * @return QueryBuilder
     */
    private function getEventTicketsCostQB(Event $event): QueryBuilder
    {
        $qb = $this->createQueryBuilder('tc');
        $qb->where($qb->expr()->eq('tc.event', ':event'))
            ->andWhere($qb->expr()->eq('tc.visible', ':visible'))
            ->setParameter(':event', $event)
            ->setParameter(':visible', true)
            ->orderBy('tc.type')
            ->orderBy('tc.sortOrder')
            ->addOrderBy('tc.amount')
        ;

        return $qb;
    }
}
