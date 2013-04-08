<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Mail
 *
 * @ORM\Table(name="event__mails")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\MailRepository")
 */
class Mail
{
    /**
     * @var int $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title = '';

    /**
     * @var string $text
     *
     * @ORM\Column(name="text", type="text")
     */
    protected $text;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event", cascade={"remove"})
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $event;

    /**
     * @var bool $start
     *
     * @ORM\Column(name="start", type="boolean")
     */
    protected $start = false;

    /**
     * @todo refact. это костыльное и временное решение
     * @var string $paymentStatus
     *
     * @ORM\Column(name="payment_status", type="string", nullable=true)
     */
    protected $paymentStatus = null;

    /**
     * @var int $totalMessages
     *
     * @ORM\Column(name="total_messages", type="integer")
     */
    protected $totalMessages = 0;

    /**
     * @var int $sentMessages
     *
     * @ORM\Column(name="sent_messages", type="integer")
     */
    protected $sentMessages = 0;

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getTitle() ?: '';
    }

    /**
     * @param int $sentMessages
     */
    public function setSentMessages($sentMessages)
    {
        $this->sentMessages = $sentMessages;
    }

    /**
     * @return int
     */
    public function getSentMessages()
    {
        return $this->sentMessages;
    }

    /**
     * @param int $totalMessages
     */
    public function setTotalMessages($totalMessages)
    {
        $this->totalMessages = $totalMessages;
    }

    /**
     * @return int
     */
    public function getTotalMessages()
    {
        return $this->totalMessages;
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
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
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
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event|null $event
     *
     * @return void
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return bool
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param bool $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * @param array $data
     *
     * @return mixed|string
     */
    public function replace($data)
    {
        $text = $this->getText();
        foreach ($data as $key => $value) {
            $text = str_replace($key, $value, $text);
        }

        return $text;
    }

    /**
     * @return null|string
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * @param string $paymentStatus
     */
    public function setPaymentStatus($paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;
    }

    /**
     * @return string
     */
    public function getStatistic()
    {
        return $this->sentMessages . '/' . $this->totalMessages . (($this->sentMessages == $this->totalMessages) ? ' - complete' : '');
    }
}
