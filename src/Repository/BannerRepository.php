<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Banner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
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
     * @param array $notInclude
     *
     * @return array|Banner[]
     */
    public function getActiveBannersWithOutIncluded(array $notInclude = []): array
    {
        $qb = $this->getActiveBannersQb();

        if (!empty($notInclude)) {
            $qb->andWhere($qb->expr()->notIn('b.id', ':not_include'))
                ->setParameter('not_include', $notInclude)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $include
     *
     * @return array|Banner[]
     */
    public function getActiveBannersIncluded(array $include = []): array
    {
        $qb = $this->getActiveBannersQb();

        if (!empty($include)) {
            $qb->andWhere($qb->expr()->in('b.id', ':include'))
                ->setParameter('include', $include)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return QueryBuilder
     */
    private function getActiveBannersQb(): QueryBuilder
    {
        $now = new \DateTime('now');

        $qb = $this->createQueryBuilder('b');

        $qb
            ->where($qb->expr()->eq('b.active', ':active'))
            ->andWhere($qb->expr()->lte('b.since', ':now'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->gte('b.till', ':now'),
                $qb->expr()->isNull('b.till')
            ))
            ->setParameter('active', true)
            ->setParameter('now', $now)
            ->orderBy('b.since', Criteria::DESC)
            ->addOrderBy('b.till', Criteria::DESC)
            ->addOrderBy('b.id', Criteria::DESC)
        ;

        return $qb;
    }
}
