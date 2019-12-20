<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Review;
use App\Entity\Sponsor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * SponsorRepository.
 */
class SponsorRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sponsor::class);
    }

    /**
     * @param Event $event
     *
     * @return array
     */
    public function getSponsorsOfEventWithCategory(Event $event)
    {
        $qb = $this->createQueryBuilder('s');

        $qb->select('s', 'c.id')
            ->join('s.sponsorEvents', 'se')
            ->join('se.category', 'c')
            ->where($qb->expr()->eq('se.event', ':event'))
            ->setParameter('event', $event->getId())
            ->orderBy('c.sortOrder', Criteria::DESC)
            ->addOrderBy('s.sortOrder', Criteria::DESC)
        ;

        return $qb->getQuery()->getResult();
    }
}
