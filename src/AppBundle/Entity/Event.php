<?php

namespace App\Entity;

use App\Traits\TranslateTrait;
use App\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * App\Entity\Event.
 *
 * @Vich\Uploadable
 *
 * @ORM\Table(name="event__events")
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
class Event implements Translatable
{
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
     * @ORM\ManyToOne(targetEntity="App\Entity\EventGroup", inversedBy="events")
     */
    private $group;

    /**
     * @var ArrayCollection|EventAudience[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\EventAudience", mappedBy="events")
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
     */
    protected $slug;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     *
     * @Gedmo\Translatable(fallback=true)
     */
    protected $city;

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
     * @var \DateTime
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
     */
    protected $ticketsCost;

    /**
     * @var bool
     *
     * @ORM\Column(name="receive_payments", type="boolean")
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
     * Background color for event card.
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
     * @ORM\OneToMany(targetEntity="EventPage", mappedBy="event")
     * @ORM\OrderBy({"sortOrder" = "DESC"})
     */
    protected $pages;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Speaker", mappedBy="events")
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     */
    protected $speakers;

    /**
     * Спикери які знаходяться на розгляді участі в евенті.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
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
     * @ORM\OneToMany(targetEntity="Ticket", mappedBy="event")
     */
    protected $tickets;

    /**
     * @Assert\File(maxSize="6000000")
     * @Assert\Image
     *
     * @Vich\UploadableField(mapping="event_image", fileNameProperty="logo")
     */
    protected $logoFile;

    /**
     * @Assert\File(maxSize="6000000")
     * @Assert\Image
     *
     * @Vich\UploadableField(mapping="event_image", fileNameProperty="smallLogo")
     */
    protected $smallLogoFile;

    /**
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
     * @Assert\File(maxSize="25165824", mimeTypes={"video/webm", "video/mp4"})
     *
     * @Vich\UploadableField(mapping="event_header_video", fileNameProperty="headerVideo")
     */
    private $headerVideoFile;

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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getDateEnd()
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
     * @param string|null $city
     *
     * @return $this
     */
    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
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
        $eventEndDate = $this->dateEnd ?: $this->date;
        $pastDate = (new \DateTime())->sub(new \DateInterval('P1D'));

        return $this->active && $eventEndDate > $pastDate;
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
     * @return string
     */
    public function getLogo()
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
     * @return bool
     */
    public function isHasAvailableTickets()
    {
        /** @var TicketCost $cost */
        foreach ($this->ticketsCost as $cost) {
            if ($cost->isEnabled() && ($cost->isUnlimited() || $cost->getCount() > $cost->getSoldCount())) {
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
        /** @var TicketCost $result */
        $result = null;
        /** @var TicketCost $ticketCost */
        foreach ($this->ticketsCost as $ticketCost) {
            if (!$result) {
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
    public function removeAudience(EventAudience $audience): self
    {
        if ($this->audiences->contains($audience)) {
            $this->audiences->removeElement($audience);
            $audience->removeEvent($this);
        }

        return $this;
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
}
