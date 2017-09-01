<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Translatable\Translatable;
use Stfalcon\Bundle\EventBundle\Traits\Translate;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Speaker
 *
 * @Vich\Uploadable
 * @ORM\Table(name="event__speakers")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\SpeakerRepository")
 * @Gedmo\TranslationEntity(class="Stfalcon\Bundle\EventBundle\Entity\Translation\SpeakerTranslation")
 */
class Speaker implements Translatable
{
    use Translate;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Stfalcon\Bundle\EventBundle\Entity\Translation\SpeakerTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;
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
     * @Gedmo\Translatable(fallback=true)
     */
    private $name = '';

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string $company
     *
     * @ORM\Column(name="company", type="string", length=255)
     */
    private $company;

    /**
     * @var text $about
     *
     * @ORM\Column(name="about", type="text")
     * @Gedmo\Translatable(fallback=true)
     */
    private $about;

    /**
     * @var string $photo
     *
     * @ORM\Column(name="photo", type="string", length=255, nullable=true)
     */
    private $photo;

    /**
     * @Assert\File(maxSize="6000000")
     * @Assert\Image
     * @Vich\UploadableField(mapping="speaker_photo", fileNameProperty="photo")
     */
    private $file;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Event", inversedBy="speakers")
     * @ORM\JoinTable(name="event__events_speakers",
     *   joinColumns={
     *     @ORM\JoinColumn(name="speaker_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     *   }
     * )
     */
    private $events;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Review", mappedBy="speakers")
     */
    private $reviews;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->reviews = new ArrayCollection();
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
     * Set photo
     *
     * @param string $photo photo
     *
     * @return $this
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $eMail
     */
    public function setEmail($eMail)
    {
        $this->email = $eMail;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set company
     *
     * @param string $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set about
     *
     * @param text $about
     */
    public function setAbout($about)
    {
        $this->about = $about;
    }

    /**
     * Get about
     *
     * @return text
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * Get photo
     *
     * @return string
     */
    public function getPhoto() {
        return $this->photo;
    }

    public function getFile() {
        return $this->file;
    }

    public function setFile($file) {
        $this->file = $file;

       $this->setUpdatedAt(new \DateTime());
    }

    public function getEvents() {
        return $this->events;
    }

    public function setEvents($events) {
        $this->events = $events;
    }

    public function getReviews() {
        return $this->reviews;
    }

    public function setReviews($reviews) {
        $this->reviews = $reviews;
    }

    public function setUpdatedAt(\DateTime $updatedAt) {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName() ?: '-';
    }
}
