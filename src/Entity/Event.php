<?php

namespace App\Entity;

use App\Model\Translatable\TranslatableInterface;
use App\Traits\TranslateTrait;
use App\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable
 *
 * @ORM\Table(name="event__events",
 *     indexes={
 *         @ORM\Index(columns={"active"}),
 *         @ORM\Index(columns={"receive_payments"}),
 *         @ORM\Index(columns={"date"}),
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 *
 * @UniqueEntity(
 *     "slug",
 *     errorPath="slug",
 *     message="Поле slug должно быть уникальное."
 * )
 *
 * @AppAssert\Event\EventBlockPositionUnique()
 *
 * @Gedmo\TranslationEntity(class="App\Entity\Translation\EventTranslation")
 */
class Event implements TranslatableInterface
{
    public const EVENT_TYPE_CONFERENCE = 'conference';
    public const EVENT_TYPE_WEBINAR = 'webinar';
    public const EVENT_TYPE_MEETUP = 'meetup';
    public const EVENT_TYPE_WORKSHOP = 'workshop';

    private const PAYMENT_TYPE_FREE = 'free';
    private const PAYMENT_TYPE_FREEMIUM = 'freemium';
    private const PAYMENT_TYPE_PAID = 'paid';

    use TranslateTrait;
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(
     *   targetEntity="App\Entity\Translation\EventTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

    /**
     * @var EventGroup|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EventGroup", inversedBy="events")
     */
    private $group;

    /**
     * @var ArrayCollection|EventAudience[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\EventAudience", inversedBy="events")
     * @ORM\JoinTable(name="events_audiences")
     *
     * @Assert\Valid()
     */
    private $audiences;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @var ArrayCollection|EventBlock[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\EventBlock",
     *      mappedBy="event", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     *
     * @Assert\Valid()
     */
    private $blocks;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     *
     * @Gedmo\Translatable(fallback=true)
     */
    protected $name = '';

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", name="seo_title", nullable=true)
     *
     * @Gedmo\Translatable(fallback=true)
     */
    private $seoTitle;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     *
     * @AppAssert\Slug\Slug()
     */
    protected $slug;

    /**
     * @var City|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\City")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $city;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     *
     * @Gedmo\Translatable(fallback=true)
     */
    protected $place;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(type="string", nullable=true, options={"default":"d MMMM Y, HH:mm"})
     */
    protected $dateFormat = 'd MMMM Y, HH:mm';

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $date;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\GreaterThan(propertyPath="date")
     */
    protected $dateEnd;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Gedmo\Translatable(fallback=true)
     *
     * @Assert\NotBlank()
     */
    protected $description;

    /**
     * Краткий текст в слайдере.
     *
     * @var string
     *
     * @Gedmo\Translatable(fallback=true)
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $about;

    /**
     * Wants to visit event users count;.
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $wantsToVisitCount = 0;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $logo;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $smallLogo;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $background;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $active = true;
    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $smallEvent = false;

    /**
     * @var float
     *
     * @ORM\Column(name="cost", type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $cost = 0;

    /**
     * @var TicketCost[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\TicketCost",
     *      mappedBy="event", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @Assert\Valid()
     *
     * @ORM\OrderBy({"sortOrder" = "ASC", "type" = "ASC", "amount" = "ASC"})
     */
    protected $ticketsCost;

    /**
     * @var bool
     *
     * @ORM\Column(name="receive_payments", type="boolean")
     *
     * @Assert\Expression(
     *     "value !== this.isFreeParticipationCost() || (!value && !this.isFreeParticipationCost())",
     *     message="Нельзя принимать оплату в бесплатном событии."
     * )
     */
    protected $receivePayments = false;

    /**
     * Можно ли применять скидку для постоянных участников.
     *
     * @var bool
     *
     * @ORM\Column(name="use_discounts", type="boolean")
     */
    protected $useDiscounts = true;

    /**
     * @var string
     *
     * Background color for event card
     *
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/\#[0-9a-fA-F]{6}$/i",
     *     match=true,
     *     message="не верный формат цвета"
     * )
     *
     * @ORM\Column(name="background_color", type="string", length=7, options={"default":"#4e4e84"})
     */
    protected $backgroundColor = '#4e4e84';

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":false}, nullable=true)
     */
    protected $useCustomBackground = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":false}, nullable=true)
     */
    protected $showLogoWithBackground = false;

    /**
     * @var EventPage[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EventPage", mappedBy="event")
     * @ORM\OrderBy({"sortOrder" = "DESC"})
     */
    protected $pages;

    /**
     * @var Speaker[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Speaker", mappedBy="events")
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     */
    protected $speakers;

    /**
     * Спикери які знаходяться на розгляді участі в евенті.
     *
     * @var Speaker[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Speaker", mappedBy="candidateEvents")
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     */
    protected $candidateSpeakers;

    /**
     * Speakers event .
     *
     * @var Speaker[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Speaker", mappedBy="committeeEvents")
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     */
    protected $committeeSpeakers;

    /**
     * Speakers event .
     *
     * @var Speaker[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="Speaker", mappedBy="expertEvents")
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     */
    protected $discussionExperts;

    /**
     * @var Ticket[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Ticket", mappedBy="event")
     */
    protected $tickets;

    /**
     * @var UploadedFile
     *
     * @Assert\File(maxSize="6000000")
     * @Assert\Image
     *
     * @Vich\UploadableField(mapping="event_image", fileNameProperty="logo")
     */
    protected $logoFile;

    /**
     * @var UploadedFile
     *
     * @Assert\File(maxSize="6000000")
     * @Assert\Image
     *
     * @Vich\UploadableField(mapping="event_image", fileNameProperty="smallLogo")
     */
    protected $smallLogoFile;

    /**
     * @var File
     *
     * @Assert\File(maxSize="6000000")
     * @Assert\Image
     *
     * @Vich\UploadableField(mapping="event_image", fileNameProperty="background")
     */
    protected $backgroundFile;

    /**
     * @var string
     *
     * @Gedmo\Translatable(fallback=true)
     *
     * @ORM\Column(name="meta_description", type="string", length=255, nullable=true)
     */
    protected $metaDescription;

    /**
     * @var bool
     *
     * @ORM\Column(name="admin_only", type="boolean")
     */
    protected $adminOnly = false;

    /**
     * @var float|null
     *
     * @ORM\Column(type="decimal", precision=12, scale=6, nullable=true)
     */
    private $lat = null;

    /**
     * @var float|null
     *
     * @ORM\Column(type="decimal", precision=12, scale=6, nullable=true)
     */
    private $lng = null;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="header_video", nullable=true)
     */
    private $headerVideo;

    /**
     * @var File
     *
     * @Assert\File(maxSize="25165824", mimeTypes={"video/webm", "video/mp4"})
     *
     * @Vich\UploadableField(mapping="event_header_video", fileNameProperty="headerVideo")
     */
    private $headerVideoFile;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="registration_open", options={"default":true})
     */
    private $registrationOpen = true;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $type;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", name="participation_cost", nullable=true, length=20)
     */
    private $participationCost;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $online = false;

    /**
     * @var ArrayCollection|TicketBenefit[]
     *
     * @ORM\OneToMany(targetEntity="TicketBenefit", mappedBy="event", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @Assert\Valid()
     */
    private $ticketBenefits;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", name="telegram_link", nullable=true)
     *
     * @Assert\Url()
     */
    private $telegramLink;

    /**
     * for between dates delimiter
     *
     * @var bool
     *
     * @ORM\Column(name="takes_more_than2days", type="boolean", options={"default":true})
     */
    private $takesMoreThan2Days = true;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->speakers = new ArrayCollection();
        $this->candidateSpeakers = new ArrayCollection();
        $this->committeeSpeakers = new ArrayCollection();
        $this->discussionExperts = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->ticketsCost = new ArrayCollection();
        $this->blocks = new ArrayCollection();
        $this->audiences = new ArrayCollection();
        $this->ticketBenefits = new ArrayCollection();
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
    public function isAdminOnly()
    {
        return $this->adminOnly;
    }

    /**
     * @param bool $adminOnly
     *
     * @return $this
     */
    public function setAdminOnly($adminOnly)
    {
        $this->adminOnly = $adminOnly;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * @param string $dateFormat
     *
     * @return $this
     */
    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @param string $metaDescription
     *
     * @return $this
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTicketsCost()
    {
        return $this->ticketsCost;
    }

    /**
     * @param mixed $ticketsCost
     *
     * @return $this
     */
    public function setTicketsCost($ticketsCost)
    {
        $this->ticketsCost = $ticketsCost;

        return $this;
    }

    /**
     * @param TicketCost $ticketCost
     *
     * @return $this
     */
    public function addTicketsCost($ticketCost)
    {
        if (!$this->ticketsCost->contains($ticketCost)) {
            $this->ticketsCost->add($ticketCost);
            $ticketCost->setEvent($this);
        }

        return $this;
    }

    /**
     * @param TicketCost $ticketCost
     *
     * @return $this
     */
    public function removeTicketsCost($ticketCost)
    {
        if ($this->ticketsCost->contains($ticketCost)) {
            $this->ticketsCost->removeElement($ticketCost);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWantsToVisitCount()
    {
        return $this->wantsToVisitCount;
    }

    /**
     * @param mixed $wantsToVisitCount
     *
     * @return $this
     */
    public function setWantsToVisitCount($wantsToVisitCount)
    {
        $this->wantsToVisitCount = $wantsToVisitCount;

        return $this;
    }

    /**
     * @return bool
     */
    public function addWantsToVisitCount()
    {
        ++$this->wantsToVisitCount;

        return true;
    }

    /**
     * @return bool
     */
    public function subtractWantsToVisitCount()
    {
        --$this->wantsToVisitCount;

        return true;
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
     * @return \DateTime
     */
    public function getDateEnd(): ?\DateTime
    {
        return $this->dateEnd;
    }

    /**
     * @param \DateTime $dateEnd
     *
     * @return $this
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * Set event name.
     *
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
     * Get event name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return City|null
     */
    public function getCity(): ?City
    {
        return $this->city;
    }

    /**
     * @param City|null $city
     *
     * @return $this
     */
    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @param string|null $place
     *
     * @return $this
     */
    public function setPlace(?string $place): self
    {
        $this->place = $place;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlace(): ?string
    {
        return $this->place;
    }

    /**
     * Get date.
     *
     * @return \DateTime|null
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set date.
     *
     * @param \DateTime|null $date
     *
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set text about event (for main page of event).
     *
     * @param string $about
     *
     * @return $this
     */
    public function setAbout($about)
    {
        $this->about = $about;

        return $this;
    }

    /**
     * Get text about event (for main page of event).
     *
     * @return string
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * Set status of activity.
     *
     * @param bool $active
     *
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Is this event active?
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function isActiveAndFuture()
    {
        $eventEndDate = $this->getEndDateFromDates();
        $now = (new \DateTime('now', new \DateTimeZone('Europe/Kiev')))->setTime(0, 0);

        return $this->active && $eventEndDate > $now;
    }

    /**
     * @param string $format
     *
     * @return bool
     */
    public function isStartAndEndDateSameByFormat(string $format = 'Y-m-d'): bool
    {
        $eventEndDate = $this->getEndDateFromDates();

        $startDate = $this->date->format($format);
        $endDate = $eventEndDate->format($format);

        return $startDate === $endDate;
    }

    /**
     * @param bool $receivePayments
     *
     * @return $this
     */
    public function setReceivePayments($receivePayments)
    {
        $this->receivePayments = $receivePayments;

        return $this;
    }

    /**
     * @return bool
     */
    public function getReceivePayments()
    {
        return $this->receivePayments;
    }

    /**
     * @return bool
     */
    public function getUseDiscounts()
    {
        return $this->useDiscounts;
    }

    /**
     * @param bool $useDiscounts
     *
     * @return $this
     */
    public function setUseDiscounts($useDiscounts)
    {
        $this->useDiscounts = $useDiscounts;

        return $this;
    }

    /**
     * Get event speakers.
     *
     * @return ArrayCollection
     */
    public function getSpeakers()
    {
        return $this->speakers;
    }

    /**
     * Get tickets for event.
     *
     * @return ArrayCollection
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * Set logo.
     *
     * @param string $logo logo
     *
     * @return $this
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Set pages.
     *
     * @param ArrayCollection $pages
     *
     * @return $this
     */
    public function setPages($pages)
    {
        $this->pages = $pages;

        return $this;
    }

    /**
     * Set speakers.
     *
     * @param ArrayCollection $speakers speakers
     *
     * @return $this
     */
    public function setSpeakers($speakers)
    {
        $this->speakers = $speakers;

        return $this;
    }

    /**
     * Set tickets.
     *
     * @param mixed $tickets tickets
     *
     * @return $this
     */
    public function setTickets($tickets)
    {
        $this->tickets = $tickets;

        return $this;
    }

    /**
     * Get path to logo.
     *
     * @return string|null
     */
    public function getLogo(): ?string
    {
        return $this->logo;
    }

    /**
     * Get event name if object treated like a string.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName() ?: '-';
    }

    /**
     * Set logoFile.
     *
     * @param UploadedFile|null $logoFile
     *
     * @return $this
     */
    public function setLogoFile($logoFile)
    {
        $this->logoFile = $logoFile;
        $this->setUpdatedAt(new \DateTime());

        return $this;
    }

    /**
     * Get logoFile.
     *
     * @return UploadedFile
     */
    public function getLogoFile()
    {
        return $this->logoFile;
    }

    /**
     * @return string
     */
    public function getSmallLogo()
    {
        return $this->smallLogo;
    }

    /**
     * @param string $smallLogo
     *
     * @return $this
     */
    public function setSmallLogo($smallLogo)
    {
        $this->smallLogo = $smallLogo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSmallLogoFile()
    {
        return $this->smallLogoFile;
    }

    /**
     * @param mixed $smallLogoFile
     *
     * @return $this
     */
    public function setSmallLogoFile($smallLogoFile)
    {
        $this->smallLogoFile = $smallLogoFile;
        $this->setUpdatedAt(new \DateTime());

        return $this;
    }

    /**
     * @todo remove this method (and try remove property)
     * Get event pages
     *
     * @return ArrayCollection
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Set cost.
     *
     * @param float $cost
     *
     * @return $this
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost.
     *
     * @return float
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @return mixed
     */
    public function getBackgroundColor()
    {
        return $this->backgroundColor;
    }

    /**
     * @param mixed $backgroundColor
     *
     * @return $this
     */
    public function setBackgroundColor($backgroundColor)
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getCandidateSpeakers()
    {
        return $this->candidateSpeakers;
    }

    /**
     * @param ArrayCollection $candidateSpeakers
     *
     * @return $this
     */
    public function setCandidateSpeakers($candidateSpeakers)
    {
        $this->candidateSpeakers = $candidateSpeakers;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getCommitteeSpeakers()
    {
        return $this->committeeSpeakers;
    }

    /**
     * @param ArrayCollection $committeeSpeakers
     *
     * @return $this
     */
    public function setCommitteeSpeakers($committeeSpeakers)
    {
        $this->committeeSpeakers = $committeeSpeakers;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSmallEvent()
    {
        return $this->smallEvent;
    }

    /**
     * @param bool $smallEvent
     *
     * @return $this
     */
    public function setSmallEvent($smallEvent)
    {
        $this->smallEvent = $smallEvent;

        return $this;
    }

    /**
     * @param string|null $type
     *
     * @return bool
     */
    public function isHasAvailableTickets(?string $type)
    {
        /** @var TicketCost $cost */
        foreach ($this->ticketsCost as $cost) {
            if ($type === $cost->getType() && $cost->isEnabled() && ($cost->isUnlimitedOrDateEnd() || $cost->getCount() > $cost->getSoldCount())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isHasAvailableTicketsWithoutType()
    {
        /** @var TicketCost $cost */
        foreach ($this->ticketsCost as $cost) {
            if ($cost->isEnabled() && ($cost->isUnlimitedOrDateEnd() || $cost->getCount() > $cost->getSoldCount())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return TicketCost|null
     */
    public function getBiggestTicketCost()
    {
        /** @var TicketCost|null $result */
        $result = null;
        /** @var TicketCost $ticketCost */
        foreach ($this->ticketsCost as $ticketCost) {
            if (!$result instanceof TicketCost) {
                $result = $ticketCost;
            }
            if ($ticketCost->getAmount() > $result->getAmount()) {
                $result = $ticketCost;
            }
        }

        return $result;
    }

    /**
     * @return EventGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param EventGroup|null $group
     *
     * @return $this
     */
    public function setGroup(?EventGroup $group): self
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getLat(): ?float
    {
        return $this->lat;
    }

    /**
     * @param float|null $lat
     *
     * @return $this
     */
    public function setLat(?float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getLng(): ?float
    {
        return $this->lng;
    }

    /**
     * @param float|null $lng
     *
     * @return $this
     */
    public function setLng(?float $lng): self
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getAudiences()
    {
        return $this->audiences;
    }

    /**
     * @param EventAudience[]|ArrayCollection $audiences
     *
     * @return $this
     */
    public function setAudiences($audiences)
    {
        $this->audiences = $audiences;

        return $this;
    }

    /**
     * @param EventAudience $audience
     *
     * @return Event
     */
    public function addAudience(EventAudience $audience): self
    {
        if (!$this->audiences->contains($audience)) {
            $this->audiences->add($audience);
            $audience->addEvent($this);
        }

        return $this;
    }

    /**
     * @param EventAudience $audience
     *
     * @return Event
     */
    public function addAudiences(EventAudience $audience): self
    {
        return $this->addAudience($audience);
    }

    /**
     * @param EventAudience $audience
     *
     * @return Event
     */
    public function removeAudience(EventAudience $audience): self
    {
        if ($this->audiences->contains($audience)) {
            $this->audiences->removeElement($audience);
            $audience->removeEvent($this);
        }

        return $this;
    }

    /**
     * @param EventAudience $audience
     *
     * @return Event
     */
    public function removeAudiences(EventAudience $audience): self
    {
        return $this->removeAudience($audience);
    }

    /**
     * @return bool
     */
    public function isUseCustomBackground()
    {
        return $this->useCustomBackground;
    }

    /**
     * @param bool $useCustomBackground
     *
     * @return $this
     */
    public function setUseCustomBackground($useCustomBackground)
    {
        $this->useCustomBackground = $useCustomBackground;

        return $this;
    }

    /**
     * @return string
     */
    public function getBackground()
    {
        return $this->background;
    }

    /**
     * @param string $background
     *
     * @return $this
     */
    public function setBackground($background)
    {
        $this->background = $background;

        return $this;
    }

    /**
     * @return bool
     */
    public function isShowLogoWithBackground()
    {
        return $this->showLogoWithBackground;
    }

    /**
     * @param bool $showLogoWithBackground
     *
     * @return $this
     */
    public function setShowLogoWithBackground($showLogoWithBackground)
    {
        $this->showLogoWithBackground = $showLogoWithBackground;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBackgroundFile()
    {
        return $this->backgroundFile;
    }

    /**
     * @param mixed $backgroundFile
     *
     * @return $this
     */
    public function setBackgroundFile($backgroundFile)
    {
        $this->backgroundFile = $backgroundFile;
        $this->setUpdatedAt(new \DateTime());

        return $this;
    }

    /**
     * @return string|null
     */
    public function getHeaderVideo(): ?string
    {
        return $this->headerVideo;
    }

    /**
     * @param string $headerVideo
     *
     * @return $this
     */
    public function setHeaderVideo($headerVideo)
    {
        $this->headerVideo = $headerVideo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeaderVideoFile()
    {
        return $this->headerVideoFile;
    }

    /**
     * @param mixed $headerVideoFile
     *
     * @return $this
     */
    public function setHeaderVideoFile($headerVideoFile): self
    {
        $this->headerVideoFile = $headerVideoFile;
        $this->setUpdatedAt(new \DateTime());

        return $this;
    }

    /**
     * @return ArrayCollection|EventBlock[]
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * @param ArrayCollection|EventBlock[] $blocks
     *
     * @return $this
     */
    public function setBlocks($blocks)
    {
        $this->blocks = $blocks;

        return $this;
    }

    /**
     * @param EventBlock $block
     *
     * @return $this
     */
    public function addBlock($block)
    {
        if (!$this->blocks->contains($block)) {
            $this->blocks->add($block);
            $block->setEvent($this);
        }

        return $this;
    }

    /**
     * @param EventBlock $block
     *
     * @return $this
     */
    public function removeBlock($block)
    {
        if ($this->blocks->contains($block)) {
            $this->blocks->removeElement($block);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    /**
     * @param string|null $seoTitle
     *
     * @return $this
     */
    public function setSeoTitle(?string $seoTitle): self
    {
        $this->seoTitle = $seoTitle;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getCurrentPrice(): ?float
    {
        foreach ($this->ticketsCost as $item) {
            if ($item->isEnabled() || $item->isHaveTemporaryCount()) {
                return $item->getAmount();
            }
        }

        return null;
    }

    /**
     * @return Speaker[]|Collection
     */
    public function getDiscussionExperts()
    {
        return $this->discussionExperts;
    }

    /**
     * @param Speaker[]|Collection $discussionExperts
     *
     * @return $this
     */
    public function setDiscussionExperts($discussionExperts): self
    {
        $this->discussionExperts = $discussionExperts;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDateFromDates(): \DateTime
    {
        return $this->dateEnd instanceof \DateTime ? $this->dateEnd : $this->date;
    }

    /**
     * @return bool
     */
    public function isRegistrationOpen(): bool
    {
        return $this->registrationOpen;
    }

    /**
     * @param bool $registrationOpen
     *
     * @return $this
     */
    public function setRegistrationOpen(bool $registrationOpen): self
    {
        $this->registrationOpen = $registrationOpen;

        return $this;
    }

    /**
     * @param mixed $event
     *
     * @return bool
     */
    public function isEqualTo($event): bool
    {
        if (!$event instanceof self) {
            return false;
        }

        if ($event->getId() !== $this->getId()) {
            return false;
        }

        return true;
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
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->online;
    }

    /**
     * @param bool $online
     *
     * @return $this
     */
    public function setOnline(bool $online): self
    {
        $this->online = $online;

        return $this;
    }

    /**
     * @return TicketBenefit[]|ArrayCollection
     */
    public function getTicketBenefits()
    {
        return $this->ticketBenefits;
    }

    /**
     * @param TicketBenefit[]|ArrayCollection $ticketBenefits
     *
     * @return $this
     */
    public function setTicketBenefits($ticketBenefits): self
    {
        $this->ticketBenefits = $ticketBenefits;

        return $this;
    }

    /**
     * @param TicketBenefit $ticketBenefit
     *
     * @return $this
     */
    public function addTicketBenefit(TicketBenefit $ticketBenefit): self
    {
        if (!$this->ticketBenefits->contains($ticketBenefit)) {
            $this->ticketBenefits->add($ticketBenefit);
            $ticketBenefit->setEvent($this);
        }

        return $this;
    }

    /**
     * @param TicketBenefit $ticketBenefit
     *
     * @return $this
     */
    public function removeTicketBenefit(TicketBenefit $ticketBenefit): self
    {
        if ($this->ticketBenefits->contains($ticketBenefit)) {
            $this->ticketBenefits->removeElement($ticketBenefit);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getParticipationCost(): ?string
    {
        return $this->participationCost;
    }

    /**
     * @param string|null $participationCost
     *
     * @return $this
     */
    public function setParticipationCost(?string $participationCost)
    {
        $this->participationCost = $participationCost;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFreeParticipationCost(): bool
    {
        return self::PAYMENT_TYPE_FREE === $this->participationCost;
    }

    /**
     * @return bool
     */
    public function isFreemiumParticipationCost(): bool
    {
        return self::PAYMENT_TYPE_FREEMIUM === $this->participationCost;
    }

    /**
     * @return bool
     */
    public function isPaidParticipationCost(): bool
    {
        return self::PAYMENT_TYPE_PAID === $this->participationCost;
    }

    /**
     * @return array|string[]
     */
    public static function getParticipationCostChoice(): array
    {
        return [
            self::PAYMENT_TYPE_FREE => self::PAYMENT_TYPE_FREE,
            self::PAYMENT_TYPE_FREEMIUM => self::PAYMENT_TYPE_FREEMIUM,
            self::PAYMENT_TYPE_PAID => self::PAYMENT_TYPE_PAID,
        ];
    }

    /**
     * @return array|string[]
     */
    public static function getTypeChoices(): array
    {
        return [
            self::EVENT_TYPE_CONFERENCE => self::EVENT_TYPE_CONFERENCE,
            self::EVENT_TYPE_MEETUP => self::EVENT_TYPE_MEETUP,
            self::EVENT_TYPE_WEBINAR => self::EVENT_TYPE_WEBINAR,
            self::EVENT_TYPE_WORKSHOP => self::EVENT_TYPE_WORKSHOP,
        ];
    }

    /**
     * @return string|null
     */
    public function getTelegramLink(): ?string
    {
        return $this->telegramLink;
    }

    /**
     * @param string|null $telegramLink
     *
     * @return $this
     */
    public function setTelegramLink(?string $telegramLink): self
    {
        $this->telegramLink = $telegramLink;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTakesMoreThan2Days(): bool
    {
        return $this->takesMoreThan2Days;
    }

    /**
     * @param bool $takesMoreThan2Days
     *
     * @return $this
     */
    public function setTakesMoreThan2Days(bool $takesMoreThan2Days): self
    {
        $this->takesMoreThan2Days = $takesMoreThan2Days;

        return $this;
    }
}
