<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Event
 *
 * @ORM\Table(name="event__events")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Entity\EventRepository")
 */
class Event
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $slug
     *
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private $slug;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var text $about
     *
     * @ORM\Column(name="about", type="text")
     */
    private $about;

    /**
     * @var string $logo
     *
     * @ORM\Column(name="logo", type="string", length=255)
     */
    private $logo;

    /**
     * @var boolean $active
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active = true;

    /**
     * @ORM\OneToMany(targetEntity="Page", mappedBy="event")
     */
    private $pages;

    /**
     * @ORM\OneToMany(targetEntity="News", mappedBy="event")
     * @ORM\OrderBy({"created_at" = "DESC"})
     */
    private $news;

    /**
     * @var Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Speaker", mappedBy="events")
     */
    private $speakers;

    /**
     * @Assert\File(maxSize="6000000")
     * @Assert\Image
     */
    private $file;

    public function __construct()
    {
        $this->speakers = new ArrayCollection();
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
     * @param type $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Get event name
     *
     * @return string
     */
    public function getName() {
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
     * @param type $about
     */
    public function setAbout($about) {
        $this->about = $about;
    }

    /**
     * Get text about event (for main page of event)
     *
     * @return type
     */
    public function getAbout() {
        return $this->about;
    }
    /**
     * Set status of activity
     *
     * @param boolean $active
     */
    public function setActive($active) {
        $this->active = $active;
    }

    /**
     * Is this event active?
     *
     * @return boolean
     */
    public function isActive() {
        return $this->active;
    }


    /**
     * Get event speakers
     *
     * @return ArrayCollection
     */
    public function getSpeakers() {
        return $this->speakers;
    }

    /**
     * Set path to logo
     *
     * @param string $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
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
     * Get event name if object treated like a string
     *
     * @return string
     */
    public function __toString() {
        return $this->name;
    }

    /**
     * Set file
     *
     * @param UploadedFile|null $file
     */
    public function setFile($file) {
        $this->file = $file;
    }

    /**
     * Get file
     *
     * @return UploadedFile
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * @todo remove this method and property
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @todo remove this method and property
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @todo remove this method (and try remove property)
     * Get event pages
     *
     * @return type
     */
    public function getPages() {
        return $this->pages;
    }

    /**
     * @todo remove this method (and try remove property)
     * Get event news
     *
     * @return ArrayCollection
     */
    public function getNews() {
        return $this->news;
    }

}