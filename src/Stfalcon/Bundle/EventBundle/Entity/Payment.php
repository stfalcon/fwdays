<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Application\Bundle\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Payment.
 *
 * @ORM\Table(name="payments")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\PaymentRepository")
 */
class Payment
{
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_RETURNED = 'returned'; //доданий для статусу, коли платіж повернений користувачу

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Кто оплатил. Т.е. провел транзакцию.
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Application\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * Сумма для оплаты.
     *
     * @var float
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2)
     */
    private $amount;

    /**
     * Базова/початкова сума платежа, до застосування промокода чи скидки.
     *
     * @var float
     *
     * @ORM\Column(name="base_amount", type="decimal", precision=10, scale=2)
     */
    private $baseAmount;

    /**
     * Використанно валюти з балансу користувача,
     * яку він отримує за рефералів або за повернення коштів при відсутності євента.
     *
     * @var float
     *
     * @ORM\Column(name="fwdays_amount", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $fwdaysAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string")
     */
    private $status = '';

    /**
     * @var string
     *
     * @ORM\Column()
     */
    private $gate = 'interkassa';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @var Ticket[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Stfalcon\Bundle\EventBundle\Entity\Ticket", mappedBy="payment")
     */
    private $tickets;

    /**
     * @var float
     *
     * @ORM\Column(name="refunded_amount", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $refundedAmount;

    /**
     * @param mixed $tickets
     */
    public function setTickets($tickets)
    {
        $this->tickets = $tickets;
    }

    /**
     * @return ArrayCollection
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
     *
     * @return bool
     */
    public function addTicket(Ticket $ticket)
    {
        return !$this->tickets->contains($ticket) && $this->tickets->add($ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return bool
     */
    public function removeTicket(Ticket $ticket)
    {
        if ($ticket->isPaid()) {
            return $this->removePaidTicket($ticket);
        }

        return $this->tickets->contains($ticket) && $this->tickets->removeElement($ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return bool
     */
    public function removePaidTicket(Ticket $ticket)
    {
        if ($this->tickets->contains($ticket)) {
            if ($ticket->isPaid()) {
                $this->refundedAmount += $ticket->getAmount();
            }
            $ticket->setPayment(null);

            return $this->tickets->removeElement($ticket);
        }

        return false;
    }

    /**
     * @return float
     */
    public function getRefundedAmount()
    {
        return $this->refundedAmount;
    }

    /**
     * @param float $refundedAmount
     *
     * @return $this
     */
    public function setRefundedAmount($refundedAmount)
    {
        $this->refundedAmount = $refundedAmount;

        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set amount.
     *
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * Get amount.
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set status.
     *
     * @param string $status
     */
    // @todo тут треба міняти на приват. і юзати методи MarkedAsPaid

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status.
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

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return bool
     */
    public function isPaid()
    {
        return self::STATUS_PAID === $this->getStatus();
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return self::STATUS_PENDING === $this->getStatus();
    }

    /**
     * @return string
     */
    public function getGate()
    {
        return $this->gate;
    }

    /**
     * @param $gate
     */
    public function setGate($gate)
    {
        $this->gate = $gate;
    }

    /**
     * Get status of payment.
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
