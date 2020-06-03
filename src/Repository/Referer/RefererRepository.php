<?php

namespace App\Repository\Referer;

use App\Entity\Referer\Referer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
