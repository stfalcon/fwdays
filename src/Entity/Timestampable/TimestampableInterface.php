<?php

declare(strict_types=1);

namespace App\Entity\Timestampable;

/**
 * TimestampableInterface.
 */
interface TimestampableInterface
{
    /**
     * @param \DateTimeImmutable $createdAt
     *
     * @return self
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt);

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable;

    /**
     * @param \DateTime $createdAt
     *
     * @return self
     */
    public function setUpdatedAt(\DateTime $createdAt);

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime;

    /**
     * Init timestampable fields.
     */
    public function initTimestampableFields(): void;
}
