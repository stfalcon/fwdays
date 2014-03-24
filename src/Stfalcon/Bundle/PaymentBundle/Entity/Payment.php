<?php

namespace Stfalcon\Bundle\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Application\Bundle\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Stfalcon\Bundle\EventBundle\Entity\PromoCode;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;

/**
 * Stfalcon\Bundle\PaymentBundle\Entity\Payment
 *
 * @ORM\Table(name="payments")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\PaymentBundle\Entity\PaymentRepository")
 */
class Payment
{
    const STATUS_PENDING = 'pending';
    const STATUS_PAID    = 'paid';

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
     * @param Ticket $ticket
     */
    public function addTicket(Ticket $ticket)
    {
        $this->amount += $ticket->getAmount();
        $this->tickets->add($ticket);
    }

    /**
     * @param PromoCode $promoCode
     */
    public function addPromoCodeForTickets($promoCode)
    {
        foreach ($this->tickets as $ticket) {
            if (!$ticket->getHasDiscount()) {
                $ticket->setPromoCode($promoCode);
                $amountWithDiscount = $ticket->getAmountWithoutDiscount() - ($ticket->getAmountWithoutDiscount() * ($promoCode->getDiscountAmount() / 100));
                $ticket->setAmount($amountWithDiscount);
            }
        }
        $this->recalculateAmount();
    }

    /**
     * Recalculate amount of payment
     */
    public function recalculateAmount()
    {
        $this->amount = 0;
        foreach ($this->tickets as $ticket) {
            $this->amount += $ticket->getAmount();
        }
    }

    /**
     * @return null|PromoCode
     */
    public function getPromoCodeFromTickets()
    {
        $promoCode = null;
        foreach ($this->tickets as $ticket) {
            if ($promoCode = $ticket->getPromoCode()) {
                return $promoCode;
            }
        }

        return $promoCode;
    }

    /**
     * Get ticket number for payment
     *
     * @return int|void
     */
    public function getTicketNumber()
    {
        /** @var ArrayCollection $tickets */
        $tickets = $this->getTickets();

        if (!$tickets->isEmpty()) {
            return $tickets->first()->getId();
        }

        return ;
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
        return (string) $this->getStatus() ?: '-';
    }
}
