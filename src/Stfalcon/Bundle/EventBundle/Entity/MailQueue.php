<?php
/**
 * Created by JetBrains PhpStorm.
 * User: zion
 * Date: 15.03.13
 * Time: 11:08
 * To change this template use File | Settings | File Templates.
 */

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
     * @var integer $id
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
     * @ORM\ManyToOne(targetEntity="Stfalcon\Bundle\EventBundle\Entity\Mail")
     * @ORM\JoinColumn(name="mail_id", referencedColumnName="id")
     */
    private $mail;

    /**
     * @var boolean $isSent
     *
     * @ORM\Column(name="is_sent", type="boolean")
     */
    private $isSent = false;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param boolean $isSent
     */
    public function setIsSent($isSent)
    {
        $this->isSent = $isSent;
    }

    /**
     * @return boolean
     */
    public function getIsSent()
    {
        return $this->isSent;
    }

    /**
     * @param int $mail
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    /**
     * @return int
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