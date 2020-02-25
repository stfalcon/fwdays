<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\PromoCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * PromoCodeRepository.
 */
class PromoCodeRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PromoCode::class);
    }

    /**
     * @param string $code
     * @param Event  $event
     *
     * @return PromoCode|null
     *
     * @throws \Exception
     */
    public function findActivePromoCodeByCodeAndEvent($code, $event): ?PromoCode
    {
        $qb = $this->createQueryBuilder('pc');
        $qb->andWhere($qb->expr()->eq('pc.event', ':event'))
            ->andWhere($qb->expr()->eq('pc.code', ':code'))
            ->andWhere($qb->expr()->gte('pc.endDate', ':now'))
            ->setParameters(new ArrayCollection(
                [
                    new Parameter('code', $code),
                    new Parameter('event', $event),
                    new Parameter('now', new \DateTime()),
                ]
            ))
            ->setMaxResults(1)
        ;
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            $result = null;
        }

        return $result;
    }
}
