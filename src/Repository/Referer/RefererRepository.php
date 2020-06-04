<?php

namespace App\Repository\Referer;

use App\Entity\Referer\Referer;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * RefererRepository.
 */
class RefererRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Referer::class);
    }

    /**
     * @param string $cookieId
     *
     * @return Referer[]
     */
    public function findAllWithCookieId(string $cookieId): array
    {
        $qb = $this->createQueryBuilder('r');
        $qb->where($qb->expr()->isNull('r.user'))
            ->andWhere($qb->expr()->eq('r.cookieId', ':cookieId'))
            ->setParameter('cookieId', $cookieId)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param User      $user
     * @param \DateTime $dateTime
     *
     * @return Referer[]
     */
    public function findAllByUserBeforeDate(User $user, \DateTime $dateTime)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->where($qb->expr()->eq('r.user', ':user'))
            ->andWhere($qb->expr()->lt('r.date', ':date'))
            ->setParameters(new ArrayCollection(
                    [
                        new Parameter('user', $user),
                        new Parameter('date', $dateTime),
                    ]
                )
            )
            ->setMaxResults(10)
            ->orderBy('r.date', Criteria::DESC)
        ;

        return $qb->getQuery()->getResult();
    }
}
