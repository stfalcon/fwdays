<?php

namespace Stfalcon\Bundle\EventBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\PromoCode;

/**
 * PromoCodeRepository
 *
 */
class PromoCodeRepository extends EntityRepository
{
    /**
     * @param string $code
     * @param Event  $event
     *
     * @return PromoCode|null
     */
    public function findActivePromoCodeByCodeAndEvent($code, $event)
    {
        $qb = $this->createQueryBuilder('pc')
            ->andWhere('pc.event = :event')
            ->andWhere('pc.code = :code')
            ->andWhere('pc.endDate >= :endDate')
            ->setParameter('code', $code)
            ->setParameter('event', $event)
            ->setParameter('endDate', new \DateTime())
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}