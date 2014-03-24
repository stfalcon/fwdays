<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Stfalcon\Bundle\EventBundle\Entity\PromoCode
 *
 * @ORM\Table(name="event__promo_code")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\PromoCodeRepository")
 */
class PromoCode
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $title = '';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $discountAmount;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $code;


    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $event;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $endDate;

    public function __construct()
    {
        $this->code = substr(strtoupper(md5(uniqid())), 0, 10);
        $this->discountAmount = 10;
        $this->endDate = new \DateTime('+10 days');
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $discountAmount
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->discountAmount = $discountAmount;
    }

    /**
     * @return int
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \Stfalcon\Bundle\EventBundle\Entity\Event $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return \Stfalcon\Bundle\EventBundle\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->title . ' - ' . $this->discountAmount . '%' . ' (' . $this->code . ')';
    }
}
