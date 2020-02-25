<?php

declare(strict_types=1);

namespace App\Traits;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * EntityManagerTrait.
 */
trait EntityManagerTrait
{
    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     *
     * @required
     */
    public function setEntityManager(EntityManager $em): void
    {
        $this->em = $em;
    }

    /**
     * @param object $object
     * @param bool   $withFlush
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function persistAndFlush($object, bool $withFlush = true): void
    {
        $this->em->persist($object);
        if ($withFlush) {
            $this->em->flush();
        }
    }
}
