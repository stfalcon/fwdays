<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * App\Entity\MailQueue.
 *
 * @ORM\Table(name="event__mails_queues")
 * @ORM\Entity()
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
     */
    private $user;

    /**
     * @var Mail
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Mail", inversedBy="mailQueues", cascade={"persist"})
     * @ORM\JoinColumn(name="mail_id", referencedColumnName="id", onDelete="CASCADE")
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
     * @return MailQueue
     */
    public function setIsOpen($isOpen = true)
    {
        $this->isOpen = $isOpen;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsOpen()
    {
        return $this->isOpen;
    }

    /**
     * @param bool $isUnsubscribe
     *
     * @return MailQueue
     */
    public function setIsUnsubscribe($isUnsubscribe = true)
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
    public function getIsSent()
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
     * @return Mail
     */
    public function getMail()
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
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
