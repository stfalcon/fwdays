<?php

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
     * @ORM\Column(type="datetime_immutable")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;

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
