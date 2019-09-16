<?php

namespace Application\Bundle\DefaultBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Translatable\Translatable;
use Application\Bundle\DefaultBundle\Traits\TranslateTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Application\Bundle\DefaultBundle\Entity\Speaker.
 *
 * @Vich\Uploadable
 *
 * @ORM\Table(name="event__speakers")
 * @ORM\Entity(repositoryClass="Application\Bundle\DefaultBundle\Repository\SpeakerRepository")
 *
 * @UniqueEntity(
 *     "slug",
 *     errorPath="slug",
 *     message="Поле slug повинне бути унікальне."
 * )
 * @Gedmo\TranslationEntity(class="Application\Bundle\DefaultBundle\Entity\Translation\SpeakerTranslation")
 */
class Speaker implements Translatable
{
    use TranslateTrait;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Application\Bundle\DefaultBundle\Entity\Translation\SpeakerTranslation",
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
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @Gedmo\Translatable(fallback=true)
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=255)
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(name="about", type="text")
     *
     * @Gedmo\Translatable(fallback=true)
     */
    private $about;

    /**
     * @var string
     *
     * @ORM\Column(name="photo", type="string", length=255, nullable=true)
     */
    private $photo;

    /**
     * @Assert\File(maxSize="6000000")
     * @Assert\Image(minHeight=232, minWidth=232)
     *
     * @Vich\UploadableField(mapping="speaker_photo", fileNameProperty="photo")
     */
    private $file;

    /**
     * @var Collection
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
     * Євенти в яких спикер знаходиться на розгляді.
     *
     * @var Event[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="Event", inversedBy="candidateSpeakers")
     * @ORM\JoinTable(name="event_speakers_candidate")
     */
    private $candidateEvents;

    /**
     * Євенти в яких спикер знаходиться на розгляді.
     *
     * @var Event[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="Event", inversedBy="committeeSpeakers")
     * @ORM\JoinTable(name="event_speakers_committee")
     */
    private $committeeEvents;

    /**
     * @var Event[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="Event", inversedBy="discussionExperts")
     * @ORM\JoinTable(name="event_speakers_expert")
     */
    private $expertEvents;

    /**
     * @var Review[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="Review", mappedBy="speakers")
     */
    private $reviews;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @var int
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=false, options={"default":"1"})
     */
    protected $sortOrder = 1;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->candidateEvents = new ArrayCollection();
        $this->committeeEvents = new ArrayCollection();
        $this->expertEvents = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     *
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

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
     * Set photo.
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
     * Set name.
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
     * Get name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email.
     *
     * @param string $eMail
     *
     * @return $this
     */
    public function setEmail($eMail)
    {
        $this->email = $eMail;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set company.
     *
     * @param string $company
     *
     * @return $this
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company.
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set about.
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
     * Get about.
     *
     * @return string
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * Get photo.
     *
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param File $file
     *
     * @return $this
     */
    public function setFile($file): self
    {
        $this->file = $file;

        $this->setUpdatedAt(new \DateTime());

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
     * @param Event[]|Collection $events
     *
     * @return $this
     */
    public function setEvents($events): self
    {
        $this->events = $events;

        return $this;
    }

    /**
     * @return Event[]|Collection
     */
    public function getCommitteeEvents()
    {
        return $this->committeeEvents;
    }

    /**
     * @param Event[]|Collection $committeeEvents
     *
     * @return $this
     */
    public function setCommitteeEvents($committeeEvents): self
    {
        $this->committeeEvents = $committeeEvents;

        return $this;
    }

    /**
     * @return Event[]|Collection
     */
    public function getCandidateEvents()
    {
        return $this->candidateEvents;
    }

    /**
     * @param Event[]|Collection $candidateEvents
     *
     * @return $this
     */
    public function setCandidateEvents($candidateEvents): self
    {
        $this->candidateEvents = $candidateEvents;

        return $this;
    }

    /**
     * @return Review[]|Collection
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * @param Review[]|Collection $reviews
     *
     * @return $this
     */
    public function setReviews($reviews): self
    {
        $this->reviews = $reviews;

        return $this;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName() ?: '-';
    }

    /**
     * @return Event[]|Collection
     */
    public function getExpertEvents()
    {
        return $this->expertEvents;
    }

    /**
     * @param Event[]|Collection $expertEvents
     *
     * @return $this
     */
    public function setExpertEvents($expertEvents): self
    {
        $this->expertEvents = $expertEvents;

        return $this;
    }
}
