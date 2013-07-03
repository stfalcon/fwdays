<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Application\Bundle\UserBundle\Entity\User;
use Stfalcon\Bundle\EventBundle\Entity\Mail;

/**
 * Stfalcon\Bundle\EventBundle\Entity\MailQueue
 *
 * @ORM\Table(name="event__mails_queues")
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\MailQueueRepository")
 */
class MailQueue
{
    /**
     * @var int $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Application\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var Mail
     *
     * @ORM\ManyToOne(targetEntity="Stfalcon\Bundle\EventBundle\Entity\Mail", cascade={"remove"})
     * @ORM\JoinColumn(name="mail_id", referencedColumnName="id",  onDelete="CASCADE")
     */
    private $mail;

    /**
     * @var bool $isSent
     *
     * @ORM\Column(name="is_sent", type="boolean")
     */
    private $isSent = false;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getUser() && $this->getMail()
            ? $this->getMail() . ' => ' . $this->getUser()
            : '';
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
     */
    public function setIsSent($isSent)
    {
        $this->isSent = $isSent;
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
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
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
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
