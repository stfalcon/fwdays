<?php

namespace Stfalcon\Bundle\EventBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\PromoCode;

/**
 * PromoCodeRepository.
 */
class PromoCodeRepository extends EntityRepository
{
    /**
     * @param string $code
     * @param Event  $event
     *
     * @return PromoCode|null
     *
     * @throws \Exception
     */
    public function findActivePromoCodeByCodeAndEvent($code, $event)
    {
        $qb = $this->createQueryBuilder('pc');
        $qb->andWhere($qb->expr()->eq('pc.event', ':event'))
            ->andWhere($qb->expr()->eq('pc.code', ':code'))
            ->andWhere($qb->expr()->gte('pc.endDate', ':now'))
            ->setParameter('code', $code)
            ->setParameter('event', $event)
            ->setParameter('now', new \DateTime())
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
