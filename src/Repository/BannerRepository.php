<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Banner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * BannerRepository.
 */
class BannerRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Banner::class);
    }

    /**
     * @return array|Banner[]
     */
    public function getActiveBanners(): array
    {
        $now = new \DateTime('now');

        $qb = $this->createQueryBuilder('b');

        $qb
            ->where($qb->expr()->eq('b.active', ':active'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->lte('b.since', ':now'),
                $qb->expr()->isNull('b.since')
            ))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->gte('b.till', ':now'),
                $qb->expr()->isNull('b.till')
            ))
            ->setParameters(new ArrayCollection([
                new Parameter('active', true),
                new Parameter('now', $now),
            ]))
            ->orderBy('b.since', Criteria::DESC)
            ->addOrderBy('b.till', Criteria::DESC)
            ->addOrderBy('b.id', Criteria::DESC)
        ;

        return $qb->getQuery()->getResult();
    }
}
