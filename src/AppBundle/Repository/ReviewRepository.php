<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Speaker;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Parameter;

/**
 * ReviewRepository.
 */
class ReviewRepository extends EntityRepository
{
    /**
     * Find reviews of speaker for event.
     *
     * @param Speaker $speaker
     * @param Event   $event
     *
     * @return array
     */
    public function findReviewsOfSpeakerForEvent(Speaker $speaker, Event $event): array
    {
        $qb = $this->createQueryBuilder('r');

        $qb
            ->join('r.speakers', 's')
            ->where($qb->expr()->eq('s.id', ':speaker'))
            ->andWhere($qb->expr()->eq('r.event', ':event'))
            ->setParameters(
                new ArrayCollection([
                    new Parameter('speaker', $speaker),
                    new Parameter('event', $event),
                ])
            )
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Event $event
     *
     * @return array
     */
    public function findReviewsByEvent(Event $event): array
    {
        $qb = $this->createQueryBuilder('r');

        $qb->where($qb->expr()->eq('r.event', ':event'))
            ->setParameter('event', $event);

        return $qb->getQuery()->getResult();
    }
}
