<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Stfalcon\Bundle\PaymentsBundle\Entity\Payment;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Ticket
 *
 * @ORM\Table(name="event__tickets")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\TicketRepository")
 */
class Ticket
{

    const STATUS_NEW = "new";

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @var Stfalcon\Bundle\EventBundle\Entity\Event
     *
     * @ORM\OneToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;

    /**
     * @var Stfalcon\Bundle\PaymentsBundle\Entity\Payment
     *
     * @ORM\OneToOne(targetEntity="Stfalcon\Bundle\PaymentsBundle\Entity\Payment")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id")
     */
    private $payment;

    /**
     * @var Application\Bundle\UserBundle\Entity\User
     *
     * @ORM\OneToOne(targetEntity="Application\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string")
     */
    private $status;

    /**
     * @return Stfalcon\Bundle\EventBundle\Entity\Event
     */
    public function getEvent() {
        return $this->event;
    }

    /**
     * @param $event
     * @return void
     */
    public function setEvent($event) {
        $this->event = $event;
    }

    /**
     * @return Stfalcon\Bundle\PaymentsBundle\Entity\Payment
     */
    public function getPayment() {
        return $this->payment;
    }

    /**
     * @param Stfalcon\Bundle\PaymentsBundle\Entity\Payment $payment
     * @return void
     */
    public function setPayment($payment) {
        $this->payment = $payment;
    }

    /**
     * @param \Application\Bundle\UserBundle\Entity\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return \Application\Bundle\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * This is a new ticket?
     *
     * @return bool
     */
    public function isNew()
    {
        return (bool) $this->getStatus() == self::STATUS_NEW;
    }

}