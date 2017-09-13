<?php

namespace Application\Bundle\DefaultBundle\Entity;

use Stfalcon\Bundle\EventBundle\Entity\Event;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="event__ticketsCost")
 * @ORM\Entity(repositoryClass="Application\Bundle\DefaultBundle\Repository\TicketCostRepository")
 */
class TicketCost
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
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Stfalcon\Bundle\EventBundle\Entity\Event", inversedBy="ticketsCost")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $event;
    /**
     * @var int $count
     *
     * @ORM\Column(name="count", type="integer", nullable=true)
     */
    private $count;
    /**
     * Сумма для оплаты
     *
     * @var float $amount
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2)
     */
    private $amount;

    /**
     * Альтернативна сума оплати
     * @var string $altAmount
     *
     * @ORM\Column(name="alt_amount", type="string", length=10, nullable=true)
     */
    private $altAmount = '';
    /**
     * @var int $soldCount
     *
     * @ORM\Column(name="sold_count", type="integer", nullable=true)
     */
    private $soldCount = 0;

    /**
     * @var bool $enabled
     * @ORM\Column(name="enabled", type="boolean", nullable=false, options={"default":"1"})
     */
    private $enabled = true;

    /**
     * @var bool $enabled
     * @ORM\Column(name="unlimited", type="boolean", nullable=false, options={"default":"0"})
     */
    private $unlimited = false;

    /**
     * @var string $name
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAltAmount()
    {
        return $this->altAmount;
    }

    /**
     * @param string $altAmount
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
     * @return $this
     */
    public function setCount($count)
    {
        $this->count = $count;
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
     * @param float $amount
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
     * @return $this
     */
    public function setSoldCount($soldCount)
    {
        $this->soldCount = $soldCount;
        return $this;
    }

    public function incSoldCount()
    {
        $this->soldCount ++;
        if (!$this->unlimited) {
            $this->setEnabled($this->count > $this->soldCount);
        }
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
     * @param bool $unlimited
     * @return $this
     */
    public function setUnlimited($unlimited)
    {
        $this->unlimited = $unlimited;
        return $this;
    }
}