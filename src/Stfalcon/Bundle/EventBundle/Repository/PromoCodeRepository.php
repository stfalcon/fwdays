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
     * @param Event  $event
     * @param string $code
     *
     * @return PromoCode|null
     */
    public function findActivePromoCodeForEventByCode(Event $event, $code)
    {
        $qb = $this->createQueryBuilder('pc')
            ->andWhere('pc.event = :event')
            ->andWhere('pc.code = :code')
            ->andWhere('pc.endDate >= :endDate')
            ->setParameter('code', $code)
            ->setParameter('event', $event)
            ->setParameter('endDate', new \DateTime())
            ->setMaxResults(1);
        // @todo а може код просто має бути унікальним (в базі) і тоді не треба setMaxResults && getOneOrNullResult?
        return $qb->getQuery()->getOneOrNullResult();
    }
}