<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * MailQueue.
 *
 * @ORM\Table(name="event__mails_queues")
 *
 * @ORM\Entity(repositoryClass="App\Repository\MailQueueRepository")
 */
class MailQueue
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Assert\NotNull()
     */
    private $user;

    /**
     * @var Mail
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Mail", inversedBy="mailQueues", cascade={"persist"})
     * @ORM\JoinColumn(name="mail_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Assert\NotNull()
     */
    private $mail;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_sent", type="boolean")
     */
    private $isSent = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_open", type="boolean")
     */
    private $isOpen = false;
    /**
     * @var bool
     *
     * @ORM\Column(name="is_unsubscribe", type="boolean")
     */
    private $isUnsubscribe = false;

    /**
     * @param bool $isOpen
     *
     * @return $this
     */
    public function setIsOpen($isOpen = true): self
    {
        $this->isOpen = $isOpen;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsOpen(): bool
    {
        return $this->isOpen;
    }

    /**
     * @param bool $isUnsubscribe
     *
     * @return $this
     */
    public function setIsUnsubscribe($isUnsubscribe = true): self
    {
        $this->isUnsubscribe = $isUnsubscribe;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsUnsubscribe()
    {
        return $this->isUnsubscribe;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getMail().' => '.$this->getUser();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param bool $isSent
     *
     * @return $this
     */
    public function setIsSent($isSent): self
    {
        $this->isSent = $isSent;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsSent(): bool
    {
        return $this->isSent;
    }

    /**
     * @param Mail $mail
     *
     * @return $this
     */
    public function setMail($mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * @return Mail|null
     */
    public function getMail(): ?Mail
    {
        return $this->mail;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser($user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }
}
