<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\EventGroup;
use App\Entity\User;
use App\Entity\UserEventRegistration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * EventRepository.
 */
class EventRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @param User   $user
     * @param bool   $active
     * @param string $sort
     *
     * @return array
     */
    public function getSortedUserWannaVisitEventsByActive(User $user, $active = true, $sort = Criteria::ASC): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb
            ->join(UserEventRegistration::class, 'ur', 'WITH', 'ur.user = :user AND ur.event = e')
            ->where($qb->expr()->eq('e.active', ':active'))
            ->setParameters(
                new ArrayCollection([
                    new Parameter('user', $user),
                    new Parameter('active', $active),
                ])
            )
            ->orderBy('e.date', $sort);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param EventGroup $eventGroup
     *
     * @return Event|null
     */
    public function findFutureEventFromSameGroup(EventGroup $eventGroup): ?Event
    {
        $qb = $this->createQueryBuilder('e');
        $qb
            ->where($qb->expr()->eq('e.active', ':active'))
            ->andWhere($qb->expr()->gte('e.date', ':date'))
            ->andWhere($qb->expr()->eq('e.group', ':group'))
            ->andWhere($qb->expr()->eq('e.type', ':type'))
            ->setParameters(
                new ArrayCollection([
                    new Parameter('active', true),
                    new Parameter('group', $eventGroup),
                    new Parameter('date', new \DateTime()),
                    new Parameter('type', Event::EVENT_TYPE_CONFERENCE),
                ])
            )
            ->orderBy('e.date', Criteria::ASC)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
