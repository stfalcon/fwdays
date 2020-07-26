<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="event__ticketsCost")
 * @ORM\Entity(repositoryClass="App\Repository\TicketCostRepository")
 */
class TicketCost
{
    public const TYPE_FREE = 'free';
    public const TYPE_STANDARD = 'standard';
    public const TYPE_PREMIUM = 'premium';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="ticketsCost")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Ticket",
     *      mappedBy="ticketCost",
     *      cascade={"persist"})
     */
    private $tickets;

    /**
     * @var int
     *
     * @ORM\Column(name="count", type="integer", nullable=true)
     *
     * @Assert\GreaterThanOrEqual(0)
     */
    private $count;

    /**
     * Сумма для оплаты.
     *
     * @var float
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2)
     *
     * @Assert\GreaterThanOrEqual(0)
     */
    private $amount;

    /**
     * Альтернативна сума оплати.
     *
     * @var float
     *
     * @ORM\Column(name="alt_amount", type="decimal", precision=10, scale=2)
     *
     * @Assert\GreaterThanOrEqual(0)
     */
    private $altAmount;

    /**
     * @var int
     *
     * @ORM\Column(name="sold_count", type="integer", nullable=true)
     */
    private $soldCount = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false, options={"default":"1"})
     */
    private $enabled = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="unlimited", type="boolean", nullable=false, options={"default":"0"})
     */
    private $unlimited = false;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     *
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var int
     */
    private $temporaryCount = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="tickets_run_out", type="boolean", options={"default":false})
     */
    private $ticketsRunOut = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="coming_soon", type="boolean", options={"default":false})
     */
    private $comingSoon = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="visible", type="boolean", options={"default":true})
     */
    private $visible = true;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true, options={"default":null})
     */
    private $endDate = null;

    /**
     * @var int|null
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
     */
    private $sortOrder;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true, length=20)
     */
    private $type;

    /**
     * TicketCost constructor.
     */
    public function __construct()
    {
        $this->tickets = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getAmountByTemporaryCount(): float
    {
        ++$this->temporaryCount;

        return $this->getAmount();
    }

    /**
     * @return bool
     */
    public function isHaveTemporaryCount(): bool
    {
        return ($this->isUnlimitedOrDateEnd() || ($this->soldCount + $this->temporaryCount) < $this->count) && $this->enabled;
    }

    /**
     * @return mixed
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * @param ArrayCollection $tickets
     *
     * @return $this
     */
    public function setTickets($tickets)
    {
        $this->tickets = $tickets;

        return $this;
    }

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function addTicket($ticket)
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
        }

        return $this;
    }

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function removeTicket($ticket)
    {
        if ($this->tickets->contains($ticket)) {
            $this->tickets->removeElement($ticket);
        }

        return $this;
    }

    /**
     * @return float|null
     */
    public function getAltAmount(): ?float
    {
        return $this->altAmount;
    }

    /**
     * @param float $altAmount
     *
     * @return $this
     */
    public function setAltAmount($altAmount)
    {
        $this->altAmount = $altAmount;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

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
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     *
     * @return $this
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
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
     * @return int
     */
    public function getSoldCount()
    {
        return $this->soldCount;
    }

    /**
     * @param int $soldCount
     *
     * @return $this
     */
    public function setSoldCount($soldCount)
    {
        $this->soldCount = $soldCount;

        return $this;
    }

    /**
     * @return int
     */
    public function recalculateSoldCount()
    {
        $soldCount = 0;
        /** @var Ticket $ticket */
        foreach ($this->getTickets() as $ticket) {
            if ($ticket->isPaid()) {
                ++$soldCount;
            }
        }
        $this->soldCount = $soldCount;

        if (!$this->isUnlimitedOrDateEnd() && $this->isEnabled()) {
            $this->setEnabled($this->count > $this->soldCount);
        }

        return $this->soldCount;
    }

    /**
     * @return $this
     */
    public function decSoldCount()
    {
        --$this->soldCount;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUnlimited()
    {
        return $this->unlimited;
    }

    /**
     * @return bool
     */
    public function isUnlimitedOrDateEnd()
    {
        return $this->unlimited || $this->endDateIsMoreThanNow();
    }

    /**
     * @param bool $unlimited
     *
     * @return $this
     */
    public function setUnlimited($unlimited)
    {
        $this->unlimited = $unlimited;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->event->getName().'-'.$this->getName();
    }

    /**
     * @return bool
     */
    public function isTicketsRunOut(): bool
    {
        return $this->ticketsRunOut;
    }

    /**
     * @param bool $ticketsRunOut
     *
     * @return $this
     */
    public function setTicketsRunOut(bool $ticketsRunOut): self
    {
        $this->ticketsRunOut = $ticketsRunOut;

        return $this;
    }

    /**
     * @return bool
     */
    public function isComingSoon(): bool
    {
        return $this->comingSoon;
    }

    /**
     * @param bool $comingSoon
     *
     * @return $this
     */
    public function setComingSoon(bool $comingSoon): self
    {
        $this->comingSoon = $comingSoon;

        return $this;
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     *
     * @return $this
     */
    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime|null $endDate
     *
     * @return $this
     */
    public function setEndDate(?\DateTime $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return bool
     */
    public function endDateIsMoreThanNow(): bool
    {
        return ($this->endDate instanceof \DateTime) && ((new \DateTime()) <= $this->endDate);
    }

    /**
     * @return int|null
     */
    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    /**
     * @param int|null $sortOrder
     */
    public function setSortOrder(?int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * @return array|string[]
     */
    public static function getTypes(): array
    {
        return [
            'free' => self::TYPE_FREE,
            'standard' => self::TYPE_STANDARD,
            'premium' => self::TYPE_PREMIUM,
        ];
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }
}
