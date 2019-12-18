<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

/**
 * SponsorRepository.
 */
class SponsorRepository extends EntityRepository
{
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
