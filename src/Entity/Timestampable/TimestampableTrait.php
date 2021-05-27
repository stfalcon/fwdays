<?php

declare(strict_types=1);

namespace App\Entity\Timestampable;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * TimestampableTrait.
 */
trait TimestampableTrait
{
    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetimetz_immutable")
     */
    protected \DateTimeImmutable $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetimetz")
     *
     * @Gedmo\Timestampable(on="update")
     */
    protected \DateTime $updatedAt;

    /**
     * @param \DateTimeImmutable $createdAt
     *
     * @return self
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return self
     */
    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Init timestampable fields.
     */
    public function initTimestampableFields(): void
    {
        $this->createdAt = new \DateTimeImmutable('now');
        $this->updatedAt = new \DateTime('now');
    }
}
