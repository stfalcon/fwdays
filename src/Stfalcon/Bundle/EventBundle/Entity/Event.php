<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Application\Bundle\DefaultBundle\Entity\TicketCost;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Translatable\Translatable;
use Stfalcon\Bundle\EventBundle\Traits\Translate;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Event.
 *
 * @Vich\Uploadable
 *
 * @ORM\Table(name="event__events")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\EventRepository")
 *
 * @UniqueEntity(
 *     "slug",
 *     errorPath="slug",
 *     message="Поле slug должно быть уникальное."
 * )
 *
 * @Gedmo\TranslationEntity(class="Stfalcon\Bundle\EventBundle\Entity\Translation\EventTranslation")
 */
class Event implements Translatable
{
    use Translate;
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
     *   targetEntity="Stfalcon\Bundle\EventBundle\Entity\Translation\EventTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

    /**
     * @ORM\ManyToOne(targetEntity="Stfalcon\Bundle\EventBundle\Entity\EventGroup", inversedBy="events")
     */
    private $group;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

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
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     */
    protected $slug;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     *
     * @Gedmo\Translatable(fallback=true)
     */
    protected $city;

    /**
     * @var string
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
     * @ORM\OneToMany(targetEntity="Application\Bundle\DefaultBundle\Entity\TicketCost",
     *      mappedBy="event", cascade={"persist", "remove"}, orphanRemoval=true)
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
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Speaker", mappedBy="committeeEvents")
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     */
    protected $committeeSpeakers;

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
     * Constructor.
     */
    public function __construct()
    {
        $this->speakers = new ArrayCollection();
        $this->candidateSpeakers = new ArrayCollection();
        $this->committeeSpeakers = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->ticketsCost = new ArrayCollection();
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
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get event name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug.
     *
     * @param string $slug
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
     * Set city in which the conference takes place.
     *
     * @param string|null $city
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city in which the conference takes place.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set place.
     *
     * @param string|null $place
     */
    public function setPlace($place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place.
     *
     * @return string
     */
    public function getPlace()
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

    public function isActiveAndFuture()
    {
        $eventEndDate = $this->dateEnd ?: $this->date;
        $pastDate = (new \DateTime())->sub(new \DateInterval('P1D'));

        return $this->active && ($eventEndDate ? $eventEndDate > $pastDate : true);
    }

    /**
     * @param $receivePayments
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
    public function isHaveFreeTickets()
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
     * @param EventGroup $group
     *
     * @return $this
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }
}
