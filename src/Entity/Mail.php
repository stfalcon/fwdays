<?php

namespace App\Entity;

use App\Model\Translatable\TranslatableInterface;
use App\Service\LocalsRequiredService;
use App\Traits\TranslateTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * App\Entity\Mail.
 *
 * @ORM\Table(name="event__mails")
 * @ORM\Entity()
 *
 * @Gedmo\TranslationEntity(class="App\Entity\Translation\EmailTranslation")
 */
class Mail implements TranslatableInterface
{
    use TranslateTrait;

    /**
     * @ORM\OneToMany(
     *   targetEntity="App\Entity\Translation\EmailTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

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
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     *
     * @Gedmo\Translatable(fallback=true)
     *
     * @Assert\NotNull()
     */
    protected $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", nullable=false)
     *
     * @Gedmo\Translatable(fallback=true)
     *
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    protected $text;

    /**
     * @var Event[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $events;

    /**
     * @var EventAudience[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\EventAudience")
     * @ORM\JoinColumn(name="audience_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $audiences;

    /**
     * @var bool
     *
     * @ORM\Column(name="start", type="boolean")
     */
    protected $start = false;

    /**
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
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="MailQueue", mappedBy="mail", cascade={"remove", "persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="mail_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $mailQueues;

    /**
     * @var bool
     *
     * @ORM\Column(name="ignore_unsubscribe", type="boolean")
     */
    private $ignoreUnsubscribe = false;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", name="users_with_en_local", options={"unsigned"=true}, nullable=true)
     *
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $usersWithEnLocal = 0;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", name="users_with_uk_local", options={"unsigned"=true}, nullable=true)
     *
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $usersWithUkLocal = 0;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->audiences = new ArrayCollection();
        $this->mailQueues = new ArrayCollection();
        $this->translations = new ArrayCollection();
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
        return $this->getTitle() ?: '';
    }

    /**
     * @param int $sentMessages
     *
     * @return $this
     */
    public function setSentMessages($sentMessages): self
    {
        $this->sentMessages = $sentMessages;

        return $this;
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
     * Stop Mail if all mailed.
     *
     * @return $this
     */
    public function stopIfMailed()
    {
        if ($this->start) {
            $this->start = $this->totalMessages > $this->sentMessages;
        }

        return $this;
    }

    /**
     * @param int $totalMessages
     *
     * @return $this
     */
    public function setTotalMessages($totalMessages): self
    {
        $this->totalMessages = $totalMessages;

        return $this;
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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return $this
     */
    public function setText($text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return Event[]|Collection
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param Collection $events
     *
     * @return $this
     */
    public function setEvents($events): self
    {
        $this->events = $events;

        return $this;
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
     *
     * @return $this
     */
    public function setStart(bool $start): self
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * @param string $paymentStatus
     *
     * @return $this
     */
    public function setPaymentStatus($paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatistic()
    {
        return
            $this->totalMessages.'/'.$this->sentMessages.'/'.$this->openMessagesCount.'/'.$this->unsubscribeMessagesCount.(($this->sentMessages === $this->totalMessages) ? ' - complete' : '');
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
     *
     * @return $this
     */
    public function removeEvent(Event $event): self
    {
        $this->events->removeElement($event);

        return $this;
    }

    /**
     * Add mailQueue.
     *
     * @param \App\Entity\MailQueue $mailQueue
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
     * @param MailQueue $mailQueue
     *
     * @return $this
     */
    public function removeMailQueue(MailQueue $mailQueue): self
    {
        $this->mailQueues->removeElement($mailQueue);

        return $this;
    }

    /**
     * Get mailQueues.
     *
     * @return Collection
     */
    public function getMailQueues()
    {
        return $this->mailQueues;
    }

    /**
     * @return EventAudience[]|Collection
     */
    public function getAudiences()
    {
        return $this->audiences;
    }

    /**
     * @param EventAudience[]|Collection $audiences
     *
     * @return $this
     */
    public function setAudiences($audiences)
    {
        $this->audiences = $audiences;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIgnoreUnsubscribe(): bool
    {
        return $this->ignoreUnsubscribe;
    }

    /**
     * @param bool $ignoreUnsubscribe
     *
     * @return $this
     */
    public function setIgnoreUnsubscribe(bool $ignoreUnsubscribe): self
    {
        $this->ignoreUnsubscribe = $ignoreUnsubscribe;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getUsersWithEnLocal(): ?int
    {
        return $this->usersWithEnLocal;
    }

    /**
     * @param int|null $usersWithEnLocal
     *
     * @return $this
     */
    public function setUsersWithEnLocal(?int $usersWithEnLocal): self
    {
        $this->usersWithEnLocal = $usersWithEnLocal;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getUsersWithUkLocal(): ?int
    {
        return $this->usersWithUkLocal;
    }

    /**
     * @param int|null $usersWithUkLocal
     *
     * @return $this
     */
    public function setUsersWithUkLocal(?int $usersWithUkLocal): self
    {
        $this->usersWithUkLocal = $usersWithUkLocal;

        return $this;
    }

    /**
     * @return $this
     */
    public function incrementUsersWithUkLocal(): self
    {
        ++$this->usersWithUkLocal;

        return $this;
    }

    /**
     * @return $this
     */
    public function incrementUsersWithEnLocal(): self
    {
        ++$this->usersWithEnLocal;

        return $this;
    }

    /**
     * @return $this
     */
    public function decrementUsersWithUkLocal(): self
    {
        if ($this->usersWithUkLocal > 0) {
            --$this->usersWithUkLocal;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function decrementUsersWithEnLocal(): self
    {
        if ($this->usersWithEnLocal > 0) {
            --$this->usersWithEnLocal;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getUsersLocalsStatistic(): string
    {
        return \sprintf('%s/%s', $this->usersWithUkLocal, $this->usersWithEnLocal);
    }

    /**
     * @param string $locale
     */
    public function processIncrementUserLocal(string $locale): void
    {
        if (LocalsRequiredService::UK_EMAIL_LANGUAGE === $locale) {
            ++$this->usersWithUkLocal;
        } elseif (LocalsRequiredService::EN_EMAIL_LANGUAGE === $locale) {
            ++$this->usersWithEnLocal;
        }
    }

    /**
     * @param string $locale
     */
    public function processDecrementUserLocal(string $locale): void
    {
        if (LocalsRequiredService::UK_EMAIL_LANGUAGE === $locale) {
            --$this->usersWithUkLocal;
        } elseif (LocalsRequiredService::EN_EMAIL_LANGUAGE === $locale) {
            --$this->usersWithEnLocal;
        }
    }
}
