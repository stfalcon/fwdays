<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Mail.
 *
 * @ORM\Table(name="event__mails")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\MailRepository")
 */
class Mail
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text")
     */
    protected $text;

    /**
     * @var Event[]
     *
     * @ORM\ManyToMany(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $events;

    /**
     * @var bool
     *
     * @ORM\Column(name="start", type="boolean")
     */
    protected $start = false;

    /**
     * @todo refact. это костыльное и временное решение
     *
     * @var string
     *
     * @ORM\Column(name="payment_status", type="string", nullable=true)
     */
    protected $paymentStatus = null;

    /**
     * @var bool
     *
     * @ORM\Column(name="wants_visit_event", type="boolean")
     */
    protected $wantsVisitEvent = false;

    /**
     * @var int
     *
     * @ORM\Column(name="total_messages", type="integer")
     */
    protected $totalMessages = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="sent_messages", type="integer")
     */
    protected $sentMessages = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="unsubscribe_messages_cnt", type="integer")
     */
    protected $unsubscribeMessagesCount = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="open_messages_cnt", type="integer")
     */
    protected $openMessagesCount = 0;

    /**
     * @var array
     *
     * @ORM\OneToMany(targetEntity="MailQueue", mappedBy="mail", cascade={"remove", "persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="mail_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $mailQueues;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->mailQueues = new ArrayCollection();
    }

    /**
     * @param int $openMessagesCount
     *
     * @return $this
     */
    public function setOpenMessagesCount($openMessagesCount)
    {
        $this->openMessagesCount = $openMessagesCount;

        return $this;
    }

    /**
     * @param int $unsubscribeMessagesCount
     *
     * @return $this
     */
    public function setUnsubscribeMessagesCount($unsubscribeMessagesCount)
    {
        $this->unsubscribeMessagesCount = $unsubscribeMessagesCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getOpenMessagesCount()
    {
        return $this->openMessagesCount;
    }

    /**
     * @return int
     */
    public function getUnsubscribeMessagesCount()
    {
        return $this->unsubscribeMessagesCount;
    }

    /**
     * Add open messages count.
     *
     * @return $this
     */
    public function addOpenMessagesCount()
    {
        ++$this->openMessagesCount;

        return $this;
    }

    /**
     * Add unsubscribe messages count.
     *
     * @return $this
     */
    public function addUnsubscribeMessagesCount()
    {
        ++$this->unsubscribeMessagesCount;

        return $this;
    }

    /**
     * @return bool
     */
    public function isWantsVisitEvent()
    {
        return $this->wantsVisitEvent;
    }

    /**
     * @param bool $wantsVisitEvent
     *
     * @return $this
     */
    public function setWantsVisitEvent($wantsVisitEvent)
    {
        $this->wantsVisitEvent = $wantsVisitEvent;

        return $this;
    }

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
     * @param bool $checkStop
     *
     * @return $this
     */
    public function incSentMessage($checkStop = true)
    {
        ++$this->sentMessages;

        if ($checkStop) {
            $this->stopIfMailed();
        }

        return $this;
    }

    /**
     * @param bool $checkStop
     *
     * @return $this
     */
    public function decSentMessage($checkStop = true)
    {
        --$this->sentMessages;

        if ($checkStop) {
            $this->stopIfMailed();
        }

        return $this;
    }

    /**
     * Stop Mail if all mailed
     *
     * @return $this
     */
    public function stopIfMailed()
    {
        if ($this->start) {
            $this->start = $this->totalMessages === $this->sentMessages;
        }

        return $this;
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
     * @param bool $checkStop
     *
     * @return $this
     */
    public function decTotalMessages($checkStop = true)
    {
        --$this->totalMessages;

        if ($checkStop) {
            $this->stopIfMailed();
        }

        return $this;
    }

    /**
     * @param bool $checkStop
     *
     * @return $this
     */
    public function incTotalMessages($checkStop = true)
    {
        ++$this->totalMessages;

        if ($checkStop) {
            $this->stopIfMailed();
        }

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
     * @return Event[]|ArrayCollection
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param ArrayCollection $events
     */
    public function setEvents($events)
    {
        $this->events = $events;
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
        return
            $this->totalMessages.'/'.
            $this->sentMessages.'/'.
            $this->openMessagesCount.'/'.
            $this->unsubscribeMessagesCount.
            (($this->sentMessages === $this->totalMessages) ? ' - complete' : '');
    }
    /**
     * Add event.
     *
     * @param Event $event
     *
     * @return Mail
     */
    public function addEvent(Event $event)
    {
        $this->events->add($event);

        return $this;
    }

    /**
     * Remove events.
     *
     * @param Event $event
     */
    public function removeEvent(Event $event)
    {
        $this->events->removeElement($event);
    }

    /**
     * Add mailQueue.
     *
     * @param \Stfalcon\Bundle\EventBundle\Entity\MailQueue $mailQueue
     *
     * @return Mail
     */
    public function addMailQueue(MailQueue $mailQueue)
    {
        $this->mailQueues->add($mailQueue);

        return $this;
    }

    /**
     * Remove mailQueue.
     *
     * @param \Stfalcon\Bundle\EventBundle\Entity\MailQueue $mailQueue
     */
    public function removeMailQueue(MailQueue $mailQueue)
    {
        $this->mailQueues->removeElement($mailQueue);
    }

    /**
     * Get mailQueues.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMailQueues()
    {
        return $this->mailQueues;
    }
}
