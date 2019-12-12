<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\PromoCode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Parameter;

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
