<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Application\Bundle\DefaultBundle\Entity\TicketCost;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Application\Bundle\UserBundle\Entity\User;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Ticket.
 *
 * @ORM\Table(name="event__tickets")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\TicketRepository")
 */
class Ticket
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
     * Сумма для оплаты.
     *
     * @var float
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2)
     */
    private $amount;

    /**
     * Сумма без учета скидки.
     *
     * @var float
     *
     * @ORM\Column(name="amount_without_discount", type="decimal", precision=10, scale=2)
     */
    private $amountWithoutDiscount;

    /**
     * @var PromoCode
     *
     * @ORM\ManyToOne(targetEntity="PromoCode")
     * @ORM\JoinColumn(name="promo_code_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $promoCode;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="tickets")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $event;

    /**
     * @var TicketCost
     *
     * @ORM\ManyToOne(targetEntity="Application\Bundle\DefaultBundle\Entity\TicketCost", inversedBy="tickets")
     * @ORM\JoinColumn(name="ticket_cost_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $ticketCost;

    /**
     * На кого выписан билет. Т.е. участник не обязательно плательщик.
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Application\Bundle\UserBundle\Entity\User", inversedBy="tickets")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var \Stfalcon\Bundle\EventBundle\Entity\Payment
     *
     * @ORM\ManyToOne(targetEntity="Stfalcon\Bundle\EventBundle\Entity\Payment", inversedBy="tickets")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $payment;

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
     * @var bool
     *
     * @ORM\Column(name="used", type="boolean")
     */
    private $used = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="has_discount", type="boolean")
     */
    private $hasDiscount = false;

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
     * @return TicketCost
     */
    public function getTicketCost()
    {
        return $this->ticketCost;
    }

    /**
     * @param TicketCost $ticketCost
     *
     * @return $this
     */
    public function setTicketCost($ticketCost)
    {
        $this->ticketCost = $ticketCost;

        return $this;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     *
     * @return $this
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPayment()
    {
        return (bool) $this->getPayment();
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param Payment|null $payment
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
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
     * @param \DateTime $createdAt
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
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Checking if ticket is "paid".
     *
     * @return bool
     */
    public function isPaid()
    {
        return $this->hasPayment() && $this->getPayment()->isPaid();
    }

    /**
     * Mark ticket as "used".
     *
     * @param bool $used
     */
    public function setUsed($used)
    {
        $this->used = $used;
    }

    /**
     * Checking if ticket is "used".
     *
     * @return bool
     */
    public function isUsed()
    {
        return $this->used;
    }

    /**
     * Generate unique md5 hash for ticket.
     *
     * @return string
     */
    public function getHash()
    {
        return md5($this->getId().$this->getCreatedAt()->format('Y-m-d H:i:s'));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getId().' ('.$this->getUser()->getFullname().')';
    }

    /**
     * @param float $amount
     *
     * @return Ticket
     */
    public function setAmount($amount)
    {
        // мы можем устанавливать/обновлять стоимость только для билетов
        // с неоплаченными платежами
//        if ($this->hasPayment() && $this->getPayment()->isPending()) {
        $this->amount = $amount;
//            $this->getPayment()->recalculateAmount();
//        }
        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amountWithoutDiscount
     *
     * @return $this
     */
    public function setAmountWithoutDiscount($amountWithoutDiscount)
    {
        $this->amountWithoutDiscount = $amountWithoutDiscount;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmountWithoutDiscount()
    {
        return $this->amountWithoutDiscount;
    }

    /**
     * @param \Stfalcon\Bundle\EventBundle\Entity\PromoCode $promoCode
     *
     * @return $this
     */
    public function setPromoCode($promoCode)
    {
        $this->promoCode = $promoCode;

        return $this;
    }

    /**
     * @return \Stfalcon\Bundle\EventBundle\Entity\PromoCode
     */
    public function getPromoCode()
    {
        return $this->promoCode;
    }

    /**
     * @return bool
     */
    public function hasPromoCode()
    {
        return !empty($this->promoCode);
    }

    /**
     * @param bool $hasDiscount
     */
    public function setHasDiscount($hasDiscount)
    {
        $this->hasDiscount = $hasDiscount;
    }

    /**
     * @return bool
     */
    public function getHasDiscount()
    {
        return $this->hasDiscount;
    }

    /**
     * @return string
     */
    public function generatePdfFilename()
    {
        return 'ticket-'.$this->getEvent()->getSlug().'.pdf';
    }
}
