<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Application\Bundle\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Stfalcon\Bundle\EventBundle\Entity\PromoCode;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Payment
 *
 * @ORM\Table(name="payments")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\PaymentRepository")
 */
class Payment
{
    const STATUS_PENDING = 'pending';
    const STATUS_PAID    = 'paid';
    const STATUS_RETURNED = 'returned'; //доданий для статусу, коли платіж повернений користувачу

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Кто оплатил. Т.е. провел транзакцию.
     *
     * @var User $user
     *
     * @ORM\ManyToOne(targetEntity="Application\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * Сумма для оплаты
     *
     * @var float $amount
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2)
     */
    private $amount;

    /**
     * Базова/початкова сума платежа, до застосування промокода чи скидки
     *
     * @var float $baseAmount
     *
     * @ORM\Column(name="base_amount", type="decimal", precision=10, scale=2)
     */
    private $baseAmount;

    /**
     * Використанно валюти з балансу користувача,
     * яку він отримує за рефералів або за повернення коштів при відсутності євента
     *
     * @var float $fwdaysAmount
     *
     * @ORM\Column(name="fwdays_amount", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $fwdaysAmount;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string")
     */
    private $status = '';

    /**
     * @var string $gate
     *
     * @ORM\Column()
     */
    private $gate = 'interkassa';

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @var Ticket[]|ArrayCollection $tickets
     *
     * @ORM\OneToMany(targetEntity="Stfalcon\Bundle\EventBundle\Entity\Ticket", mappedBy="payment")
     */
    private $tickets;

    /**
     * @param mixed $tickets
     */
    public function setTickets($tickets)
    {
        $this->tickets = $tickets;
    }

    /**
     * @return mixed
     */
    public function getTickets()
    {
        return $this->tickets;
    }
    /**
     * Constructor. Set default status to new payment.
     */
    public function __construct()
    {
        $this->setStatus(self::STATUS_PENDING);
        $this->tickets = new ArrayCollection();
    }

    /**
     * @param Ticket $ticket
     * @return bool
     */
    public function addTicket(Ticket $ticket)
    {
        return !$this->tickets->contains($ticket) && $this->tickets->add($ticket);
    }

    /**
     * @param Ticket $ticket
     * @return false
     */
    public function removeTicket(Ticket $ticket)
    {
        return $this->tickets->contains($ticket) && $this->tickets->removeElement($ticket);
    }

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
     * Set amount
     *
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set status
     *
     * @param string $status
     */
    // @todo тут треба міняти на приват. і юзати методи MarkedAsPaid
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
     * @param User $user
     */
    public function setUser(User $user)
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

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function isPaid()
    {
        return ($this->getStatus() == self::STATUS_PAID);
    }

    public function isPending()
    {
        return ($this->getStatus() == self::STATUS_PENDING);
    }

    public function getGate()
    {
        return $this->gate;
    }

    public function setGate($gate)
    {
        $this->gate = $gate;
    }

    /**
     * Get status of payment
     *
     * @return string
     */
    public function __toString()
    {
        $string = "{$this->getStatus()} (#{$this->getId()})"; // для зручності перегляду платежів в списку квитків додав id
        return $string;
    }

    public function markedAsPaid()
    {
        $this->setStatus(self::STATUS_PAID);
    }

    /**
     * @return float
     */
    public function getBaseAmount()
    {
        return $this->baseAmount;
    }

    /**
     * @param float $baseAmount
     */
    public function setBaseAmount($baseAmount)
    {
        $this->baseAmount = $baseAmount;
    }

    /**
     * @return float
     */
    public function getFwdaysAmount()
    {
        return $this->fwdaysAmount;
    }

    /**
     * @param float $fwdaysAmount
     */
    public function setFwdaysAmount($fwdaysAmount)
    {
        $this->fwdaysAmount = $fwdaysAmount;
    }
}
