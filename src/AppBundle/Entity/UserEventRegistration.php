<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     name="user_event_registration",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(columns={"user_id", "event_id"})
 *     })
 *
 * @ORM\Entity()
 *
 * @UniqueEntity({"user", "event"})
 */
class UserEventRegistration
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetimetz_immutable")
     */
    private $createdAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="eventRegistrations")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Assert\Type("App\Entity\User")
     */
    private $user;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Assert\Type("App\Entity\Event")
     */
    private $event;

    /**
     * @param User                    $user
     * @param Event                   $event
     * @param \DateTimeInterface|null $date
     *
     * @throws \Exception
     */
    public function __construct(User $user, Event $event, ?\DateTimeInterface $date = null)
    {
        $this->user = $user;
        $this->event = $event;
        if ($date instanceof \DateTime) {
            $this->createdAt = \DateTimeImmutable::createFromMutable($date);
        } elseif ($date instanceof \DateTimeImmutable) {
            $this->createdAt = $date;
        } else {
            $this->createdAt = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Kiev'));
        }
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeImmutable $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
