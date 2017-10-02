<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Translatable\Translatable;
use Stfalcon\Bundle\EventBundle\Traits\Translate;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Event
 *
 * @Vich\Uploadable
 * @ORM\Table(name="event__events")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\EventRepository")
 * @Gedmo\TranslationEntity(class="Stfalcon\Bundle\EventBundle\Entity\Translation\EventTranslation")
 */
class Event implements Translatable
{
    use Translate;
    /**
     * @var integer $id
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
     * @var string $name
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Gedmo\Translatable(fallback=true)
     */
    protected $name = '';

    /**
     * @var string $slug
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $slug;

    /**
     * @var string $city
     *
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\Translatable(fallback=true)
     */
    protected $city;

    /**
     * @var string $place
     *
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\Translatable(fallback=true)
     */
    protected $place;

    /**
     * @var string $approximateDate
     *
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\Translatable(fallback=true)
     */
    protected $approximateDate = '';

    /**
     * @var bool $useApproximateDate
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $useApproximateDate = false;

    /**
     * @var \DateTime $date
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date;

    /**
     * @var \DateTime $dateEnd
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $dateEnd;

    /**
     * @var string $description
     *
     * @ORM\Column(type="text")
     * @Gedmo\Translatable(fallback=true)
     * @Assert\NotBlank()
     */
    protected $description;

    /**
     * Краткий текст в слайдере
     *
     * @var string $about
     * @Gedmo\Translatable(fallback=true)
     * @ORM\Column(type="text", nullable=true)
     */
    protected $about;

    /**
     * Wants to visit event users count;
     *
     * @var int $wantsToVisitCount
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $wantsToVisitCount = 0;

    /**
     * @var string $logo
     *
     * @ORM\Column(type="string")
     */
    protected $logo;

    /**
     * @var string $emailBackground
     *
     * @ORM\Column(name="email_background", type="string", nullable=true)
     */
    protected $emailBackground = 'bg-common.png';

    /**
     * Фон для PDF билетов
     *
     * @var string $pdfBackgroundImage
     *
     * @ORM\Column(name="background_image", type="string", nullable=true)
     */
    protected $pdfBackgroundImage = 'left-element.png';

    /**
     * @var boolean $active
     *
     * @ORM\Column(type="boolean")
     */
    protected $active = true;

    /**
     * @var float $cost
     *
     * @ORM\Column(name="cost", type="decimal", precision=10, scale=2, nullable=false)
     */
    // @todo переименовать в Price
    protected $cost;

    /**
     * @ORM\OneToMany(targetEntity="Application\Bundle\DefaultBundle\Entity\TicketCost", mappedBy="event")
     */
    protected $ticketsCost;
    /**
     * @var boolean $receivePayments
     *
     * @ORM\Column(name="receive_payments", type="boolean")
     */
    protected $receivePayments = false;

    /**
     * Можно ли применять скидку для постоянных участников
     *
     * @var boolean $useDiscounts
     *
     * @ORM\Column(name="use_discounts", type="boolean")
     */
    protected $useDiscounts = true;

    /**
     * Background color for event card
     *
     * @var $backgroundColor
     * @Assert\NotBlank()
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
     * Спикери які знаходяться на розгляді участі в евенті
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Speaker", mappedBy="candidateEvents")
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     */
    protected $candidateSpeakers;


    /**
     * @ORM\OneToMany(targetEntity="Ticket", mappedBy="event")
     */
    protected $tickets;

    /**
     * @Assert\File(maxSize="6000000")
     * @Assert\Image
     * @Vich\UploadableField(mapping="event_image", fileNameProperty="logo")
     */
    protected $logoFile;

    /**
     * @Assert\File(maxSize="6000000")
     * @Assert\Image
     * @Vich\UploadableField(mapping="event_image", fileNameProperty="emailBackground")
     */
    protected $emailBackgroundFile;

    /**
     * @Assert\File(maxSize="6000000")
     * @Assert\Image
     * @Vich\UploadableField(mapping="event_image", fileNameProperty="pdfBackgroundImage")
     */
    protected $pdfBackgroundFile;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->speakers = new ArrayCollection();
        $this->candidateSpeakers = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->ticketsCost = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function isUseApproximateDate()
    {
        return $this->useApproximateDate;
    }

    /**
     * @param bool $useApproximateDate
     * @return $this
     */
    public function setUseApproximateDate($useApproximateDate)
    {
        $this->useApproximateDate = $useApproximateDate;
        return $this;
    }
    /**
     * @return string
     */
    public function getApproximateDate()
    {
        return $this->approximateDate;
    }

    /**
     * @param string $approximateDate
     * @return $this
     */
    public function setApproximateDate($approximateDate)
    {
        $this->approximateDate = $approximateDate;
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
     * @return $this
     */
    public function setTicketsCost($ticketsCost)
    {
        $this->ticketsCost = $ticketsCost;
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
        $this->wantsToVisitCount++;

        return true;
    }

    /**
     * @return bool
     */
    public function subtractWantsToVisitCount()
    {
        $this->wantsToVisitCount--;

        return true;
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
     * Set event name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get event name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set city in which the conference takes place
     *
     * @param string|null $city
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * Get city in which the conference takes place
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set place
     *
     * @param string|null $place
     */
    public function setPlace($place)
    {
        $this->place = $place;
        return $this;
    }

    /**
     * Get place
     *
     * @return string
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Get date
     *
     * @return \DateTime|null
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set date
     *
     * @param \DateTime|null $date
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set text about event (for main page of event)
     *
     * @param string $about
     */
    public function setAbout($about)
    {
        $this->about = $about;
        return $this;
    }

    /**
     * Get text about event (for main page of event)
     *
     * @return string
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * Set status of activity
     *
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Is this event active?
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    public function isActiveAndFuture()
    {
        $eventEndDate = $this->dateEnd ? $this->dateEnd : $this->date;
        $pastDate = (new \DateTime())->sub(new \DateInterval('P1D'));

        return $this->active && ($this->useApproximateDate || ($eventEndDate ? $eventEndDate > $pastDate : true));
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
     * @return boolean
     */
    public function getUseDiscounts() {
        return $this->useDiscounts;
    }

    /**
     * @param boolean $useDiscounts
     */
    public function setUseDiscounts($useDiscounts) {
        $this->useDiscounts = $useDiscounts;
        return $this;
    }

    /**
     * Get event speakers
     *
     * @return ArrayCollection
     */
    public function getSpeakers()
    {
        return $this->speakers;
    }

    /**
     * Get tickets for event
     *
     * @return ArrayCollection
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * Set logo
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
     * Set emailBackground
     *
     * @param string $emailBackground emailBackground
     *
     * @return $this
     */
    public function setEmailBackground($emailBackground)
    {
        $this->emailBackground = $emailBackground;

        return $this;
    }

    /**
     * Set pdfBackgroundImage
     *
     * @param string $pdfBackgroundImage pdfBackgroundImage
     *
     * @return $this
     */
    public function setPdfBackgroundImage($pdfBackgroundImage)
    {
        $this->pdfBackgroundImage = $pdfBackgroundImage;

        return $this;
    }

    /**
     * Set pages
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
     * Set speakers
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
     * Set tickets
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
     * Get path to logo
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Get path to pdfBackgroundImage
     *
     * @return string
     */
    public function getPdfBackgroundImage()
    {
        return $this->pdfBackgroundImage;
    }

    /**
     * Get event name if object treated like a string
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName() ?: '-';
    }

    /**
     * Set logoFile
     *
     * @param UploadedFile|null $logoFile
     */
    public function setLogoFile($logoFile)
    {
        $this->logoFile = $logoFile;
        return $this;
    }

    /**
     * Get logoFile
     *
     * @return UploadedFile
     */
    public function getLogoFile()
    {
        return $this->logoFile;
    }

    /**
     * Set emailBackgroundFile
     *
     * @param UploadedFile|null $emailBackgroundFile
     */
    public function setEmailBackgroundFile($emailBackgroundFile)
    {
        $this->emailBackgroundFile = $emailBackgroundFile;
        return $this;
    }

    /**
     * Get emailBackgroundFile
     *
     * @return UploadedFile
     */
    public function getEmailBackgroundFile()
    {
        return $this->emailBackgroundFile;
    }

    /**
     * Get path to emailBackground
     *
     * @return string
     */
    public function getEmailBackground()
    {
        return $this->emailBackground;
    }

    /**
     * Set pdfBackgroundFile (used in tickets PDF)
     *
     * @param UploadedFile|null $pdfBackgroundFile
     */
    public function setPdfBackgroundFile($pdfBackgroundFile)
    {
        $this->pdfBackgroundFile = $pdfBackgroundFile;
        return $this;
    }

    /**
     * Get pdfBackgroundFile
     *
     * @return UploadedFile
     */
    public function getPdfBackgroundFile()
    {
        return $this->pdfBackgroundFile;
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
     * Set cost
     *
     * @param float $cost
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
        return $this;
    }

    /**
     * Get cost
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
}
