<?php

namespace App\Repository;

use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CityRepository.
 */
class CityRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    /**
     * Find first active event.
     *
     * @return array|City[]
     */
    public function findAllActive(): array
    {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->where($qb->expr()->eq('c.active', ':active'))
            ->orderBy('c.name', Criteria::ASC)
            ->setParameter('active', true)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return City|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findDefault(): ?City
    {
        $qb = $this->createQueryBuilder('c');

        $qb->where($qb->expr()->eq('c.default', ':default'))
            ->setParameter('default', true)
        ;

        return $qb
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param string $urlName
     *
     * @return null|City
     */
    public function findOneByUrlName($urlName): ?City
    {
        $qb = $this->createQueryBuilder('c');
        $qb->where($qb->expr()->eq('c.active', ':active'))
            ->andWhere($qb->expr()->eq('c.urlName', ':urlName'))
            ->setParameters(
                new ArrayCollection([
                    new Parameter('active', true),
                    new Parameter('urlName', $urlName),
                ])
            )
        ;

        return $qb
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param int $id
     *
     * @return null|City
     */
    public function findOneById($id): ?City
    {
        $qb = $this->createQueryBuilder('c');
        $qb->where($qb->expr()->eq('c.active', ':active'))
            ->andWhere($qb->expr()->eq('c.id', ':id'))
            ->setParameters(
                new ArrayCollection([
                    new Parameter('active', true),
                    new Parameter('id', $id),
                ])
            )
        ;

        return $qb
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
