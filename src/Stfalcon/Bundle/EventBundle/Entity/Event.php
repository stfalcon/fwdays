<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Event
 *
 * @Vich\Uploadable
 * @ORM\Table(name="event__events")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\EventRepository")
 */
class Event
{
    /**
     * @var integer $id
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $name
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
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
     */
    protected $city;

    /**
     * @var string $place
     *
     * @ORM\Column(type="string", nullable=true)
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
     * @Assert\NotBlank()
     */
    protected $description;

    /**
     * @var string $about
     *
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
     * @ORM\Column(name="email_background", type="string")
     */
    protected $emailBackground;

    /**
     * @var string $sliderBackground
     *
     * @ORM\Column(name="slider_background", type="string")
     */
    protected $sliderBackground;

    /**
     * @var string $backgroundImage
     *
     * @ORM\Column(name="background_image", type="string")
     */
    protected $backgroundImage;

    /**
     * @var boolean $active
     *
     * @ORM\Column(type="boolean")
     */
    protected $active = true;

    /**
     * @var boolean $receivePayments
     *
     * @ORM\Column(name="receive_payments", type="boolean")
     */
    protected $receivePayments = false;

    /**
     * @ORM\OneToMany(targetEntity="Page", mappedBy="event")
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
     * @Assert\File(maxSize="6000000")
     * @Assert\Image
     * @Vich\UploadableField(mapping="event_image", fileNameProperty="logo")
     */
    protected $logoFile;

    /**
     * @var UploadedFile
     *
     * @Assert\File(maxSize="6000000")
     * @Assert\Image
     * @Vich\UploadableField(mapping="event_image", fileNameProperty="logoWhite")
     *
     */
    protected $logoWhiteFile;

    /**
     * @Assert\File(maxSize="6000000")
     * @Assert\Image
     * @Vich\UploadableField(mapping="event_image", fileNameProperty="emailBackground")
     */
    protected $emailBackgroundFile;

    /**
     * @Assert\File(maxSize="6000000")
     * @Assert\Image
     * @Vich\UploadableField(mapping="event_image", fileNameProperty="sliderBackground")
     */
    protected $sliderBackgroundFile;

    /**
     * @Assert\File(maxSize="6000000")
     * @Assert\Image
     * @Vich\UploadableField(mapping="event_image", fileNameProperty="backgroundImage")
     */
    protected $bgFile;

    /**
     * @var float $cost
     *
     * @ORM\Column(name="cost", type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $cost;

    /**
     * @var string
     *
     * @ORM\Column(name="logo_white", type="string")
     */
    protected $logoWhite;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->speakers      = new ArrayCollection();
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
     * Get event speakers
     *
     * @return ArrayCollection
     */
    public function getSpeakers()
    {
        return $this->speakers;
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
     * Get path to backgroundImage
     *
     * @return string
     */
    public function getBackgroundImage()
    {
        return $this->backgroundImage;
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
     * Set sliderBackgroundFile
     *
     * @param UploadedFile|null $sliderBackgroundFile
     */
    public function setSliderBackgroundFile($sliderBackgroundFile)
    {
        $this->sliderBackgroundFile = $sliderBackgroundFile;
    }

    /**
     * Get sliderBackgroundFile
     *
     * @return UploadedFile
     */
    public function getSliderBackgroundFile()
    {
        return $this->sliderBackgroundFile;
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
     * Get path to sliderBackground
     *
     * @return string
     */
    public function getSliderBackground()
    {
        return $this->sliderBackground;
    }

    /**
     * Set bgFile
     *
     * @param UploadedFile|null $bgFile
     */
    public function setBgFile($bgFile)
    {
        $this->bgFile = $bgFile;
    }

    /**
     * Get bgFile
     *
     * @return UploadedFile
     */
    public function getBgFile()
    {
        return $this->bgFile;
    }

    /**
     * @todo remove this method (and try remove property)
     * Get event pages
     *
     * @return type
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

    /**
     * @param string $logoWhite
     */
    public function setLogoWhite($logoWhite)
    {
        $this->logoWhite = $logoWhite;
    }

    /**
     * @return string
     */
    public function getLogoWhite()
    {
        return $this->logoWhite;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $logoWhiteFile
     */
    public function setLogoWhiteFile($logoWhiteFile)
    {
        $this->logoWhiteFile = $logoWhiteFile;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getLogoWhiteFile()
    {
        return $this->logoWhiteFile;
    }





}
