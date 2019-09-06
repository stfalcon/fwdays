<?php

namespace Application\Bundle\DefaultBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Application\Bundle\DefaultBundle\Entity\Payment.
 *
 * @ORM\Table(name="payments")
 * @ORM\Entity(repositoryClass="Application\Bundle\DefaultBundle\Repository\PaymentRepository")
 */
class Payment
{
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_RETURNED = 'returned'; //доданий для статусу, коли платіж повернений користувачу

    const ADMIN_GATE = 'admin';
    const INTERKASSA_GATE = 'interkassa';
    const WAYFORPAY_GATE = 'wayforpay';
    const BONUS_GATE = 'bonus';
    const PROMOCODE_GATE = 'promocode';
    const UNKNOWN_GATE = 'unknown';

    private $gates = [self::ADMIN_GATE, self::WAYFORPAY_GATE, self::BONUS_GATE, self::PROMOCODE_GATE];

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
     * @ORM\ManyToOne(targetEntity="Application\Bundle\DefaultBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Groups("payment.view")
     */
    private $user;

    /**
     * Сумма для оплаты.
     *
     * @var float
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2)
     *
     * @Groups("payment.view")
     */
    private $amount = 0;

    /**
     * Базова/початкова сума платежа, до застосування промокода чи скидки.
     *
     * @var float
     *
     * @ORM\Column(name="base_amount", type="decimal", precision=10, scale=2)
     *
     * @Groups("payment.view")
     */
    private $baseAmount = 0;

    /**
     * Використанно валюти з балансу користувача,
     * яку він отримує за рефералів або за повернення коштів при відсутності євента.
     *
     * @var float|null
     *
     * @ORM\Column(name="fwdays_amount", type="decimal", precision=10, scale=2, nullable=true)
     *
     * @Assert\GreaterThanOrEqual(0)
     *
     * @Groups("payment.view")
     */
    private $fwdaysAmount = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string")
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var string
     *
     * @ORM\Column()
     */
    private $gate = Payment::UNKNOWN_GATE;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @var Ticket[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Application\Bundle\DefaultBundle\Entity\Ticket", mappedBy="payment")
     * @ORM\OrderBy({"createdAt" = "ASC"})
     *
     * @Groups("payment.view")
     */
    private $tickets;

    /**
     * @var float|null
     *
     * @ORM\Column(name="refunded_amount", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $refundedAmount = 0;

    /**
     * @param mixed $tickets
     */
    public function setTickets($tickets)
    {
        $this->tickets = $tickets;
    }

    /**
     * @return ArrayCollection|Ticket[]
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
        $this->tickets = new ArrayCollection();
    }

    /**
     * @Groups("payment.view")
     *
     * @return int
     */
    public function getTicketCount(): int
    {
        return $this->tickets->count();
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

        if ($ticket->getTicketCost() instanceof TicketCost) {
            $ticket->getTicketCost()->recalculateSoldCount();
        }

        $ticket->setPayment(null);

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
     * @return float|null
     */
    public function getRefundedAmount(): ?float
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
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount.
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Set status.
     *
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
     *
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
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
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
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
     * @return bool
     */
    public function isReturned()
    {
        return self::STATUS_RETURNED === $this->getStatus();
    }

    /**
     * @return string
     */
    public function getGate()
    {
        return $this->gate;
    }

    /**
     * @param string $gate
     *
     * @return $this
     */
    public function setGate($gate)
    {
        $this->gate = $gate;

        return $this;
    }

    /**
     * Get status of payment.
     *
     * @return string
     */
    public function __toString()
    {
        return "{$this->getStatus()} (#{$this->getId()})"; // для зручності перегляду платежів в списку квитків додав id
    }

    /**
     * @return $this
     */
    public function markedAsPaid()
    {
        $this->setStatus(self::STATUS_PAID);

        return $this;
    }

    /**
     * @param string $gate
     *
     * @return $this
     */
    public function setPaidWithGate($gate)
    {
        $this->setStatus(self::STATUS_PAID);
        if (\in_array($gate, $this->gates, true)) {
            $this->setGate($gate);
        } else {
            $this->setGate(self::WAYFORPAY_GATE);
        }

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseAmount(): float
    {
        return $this->baseAmount;
    }

    /**
     * @param float $baseAmount
     *
     * @return $this
     */
    public function setBaseAmount($baseAmount)
    {
        $this->baseAmount = $baseAmount;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getFwdaysAmount(): ?float
    {
        return $this->fwdaysAmount;
    }

    /**
     * @param float $fwdaysAmount
     *
     * @return $this
     */
    public function setFwdaysAmount(float $fwdaysAmount): self
    {
        $this->fwdaysAmount = $fwdaysAmount;

        return $this;
    }

    /**
     * @return array
     */
    public static function getPaymentTypeChoice(): array
    {
        return [
            Payment::INTERKASSA_GATE => Payment::INTERKASSA_GATE,
            Payment::WAYFORPAY_GATE => Payment::WAYFORPAY_GATE,
            Payment::ADMIN_GATE => Payment::ADMIN_GATE,
            Payment::BONUS_GATE => Payment::BONUS_GATE,
            Payment::PROMOCODE_GATE => Payment::PROMOCODE_GATE,
            Payment::UNKNOWN_GATE => Payment::UNKNOWN_GATE,
        ];
    }

    /**
     * @return array
     */
    public static function getPaymentStatusChoice(): array
    {
        return [
            'оплачено' => Payment::STATUS_PAID,
            'ожидание' => Payment::STATUS_PENDING,
            'возращен' => Payment::STATUS_RETURNED,
        ];
    }

    /**
     * @param Payment|null $payment
     *
     * @return bool
     */
    public function isEqualTo(?Payment $payment): bool
    {
        if (!$payment instanceof self) {
            return false;
        }

        if ($payment->getId() !== $this->getId()) {
            return false;
        }

        return true;
    }
}
