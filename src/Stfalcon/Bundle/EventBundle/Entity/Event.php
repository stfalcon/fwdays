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
     * @var \DateTime $date
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date;

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
     * @ORM\OneToMany(targetEntity="EventPage", mappedBy="event")
     * @ORM\OrderBy({"sortOrder" = "DESC"})
     */
    protected $pages;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Speaker", mappedBy="events")
     */
    protected $speakers;

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
        $this->translations = new ArrayCollection();
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
     * Set event name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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

    /**
     * @param $receivePayments
     */
    public function setReceivePayments($receivePayments)
    {
        $this->receivePayments = $receivePayments;
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
}
