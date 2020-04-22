<?php

declare(strict_types=1);

namespace App\Model\Blameable;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * BlameableTrait.
 */
trait BlameableTrait
{
    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="created_by")
     */
    private $createdBy;

    /**
     * @return User|null
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * @param User|null $createdBy
     *
     * @return self
     */
    public function setCreatedBy(User $createdBy = null): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
