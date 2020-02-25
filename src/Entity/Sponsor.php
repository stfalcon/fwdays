<?php

namespace App\Entity;

use App\Model\Translatable\TranslatableInterface;
use App\Traits\TranslateTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * App\Entity\Sponsor.
 *
 * @Vich\Uploadable
 *
 * @ORM\Table(name="sponsors")
 * @ORM\Entity(repositoryClass="App\Repository\SponsorRepository")
 *
 * @Gedmo\TranslationEntity(class="App\Entity\Translation\SponsorTranslation")
 */
class Sponsor implements TranslatableInterface
{
    use TranslateTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(
     *   targetEntity="App\Entity\Translation\SponsorTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="site", type="string", nullable=true, length=255)
     *
     * @Assert\Url
     */
    protected $site;

    /**
     * @var string
     *
     * @ORM\Column(name="logo", type="string", nullable=true, length=255)
     */
    protected $logo;

    /**
     * @var int
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=false)
     *
     * @Assert\NotNull()
     */
    protected $sortOrder = 1;

    /**
     * @var string|null
     *
     * @ORM\Column(name="about", type="text", nullable=true)
     *
     * @Gedmo\Translatable(fallback=true)
     */
    protected $about;

    /**
     * @var File
     *
     * @Assert\File(maxSize="6000000")
     * @Assert\Image(minHeight=150, minWidth=280)
     *
     * @Vich\UploadableField(mapping="sponsor_image", fileNameProperty="logo")
     */
    protected $file;

    /**
     * @var EventSponsor[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\EventSponsor",
     *     mappedBy="sponsor", cascade={"persist", "remove"}, orphanRemoval=true
     * )
     */
    protected $sponsorEvents;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->sponsorEvents = new ArrayCollection();
        $this->translations = new ArrayCollection();
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
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get logo filename.
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set logo filename.
     *
     * @param string $logo
     *
     * @return $this
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Set sortOrder.
     *
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
     * Get sortOrder.
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set site.
     *
     * @param string $site
     *
     * @return $this
     */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site.
     *
     * @return string
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @return File
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
    public function setFile($file)
    {
        $this->file = $file;
        $this->setUpdatedAt(new \DateTime());

        return $this;
    }

    /**
     * @param EventSponsor $sponsorEvent
     *
     * @return $this
     */
    public function addSponsorEvent(EventSponsor $sponsorEvent)
    {
        $sponsorEvent->setSponsor($this);
        $this->sponsorEvents->add($sponsorEvent);

        return $this;
    }

    /**
     * @param EventSponsor $sponsorEvent
     *
     * @return $this
     */
    public function addSponsorEvents(EventSponsor $sponsorEvent)
    {
        $this->sponsorEvents[] = $sponsorEvent;

        return $this;
    }

    /**
     * @param EventSponsor $sponsorEvent
     *
     * @return $this
     */
    public function removeSponsorEvent(EventSponsor $sponsorEvent)
    {
        if ($this->sponsorEvents->contains($sponsorEvent)) {
            $this->sponsorEvents->removeElement($sponsorEvent);
            $sponsorEvent->setCategory(null)
                ->setEvent(null)
                ->setSponsor(null)
            ;
        }

        return $this;
    }

    /**
     * @param ArrayCollection $sponsorEvents
     *
     * @return $this
     */
    public function setSponsorEvents($sponsorEvents)
    {
        foreach ($sponsorEvents as $sponsorEvent) {
            $sponsorEvent->setSponsor($this);
        }
        $this->sponsorEvents = $sponsorEvents;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getSponsorEvents()
    {
        return $this->sponsorEvents;
    }

    /**
     * Get createdAt.
     *
     * @return \Datetime createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt.
     *
     * @param \Datetime $createdAt createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return \Datetime updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedAt.
     *
     * @param \Datetime $updatedAt updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get sponsor name if object treated like a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() ?: '-';
    }

    /**
     * @return string|null
     */
    public function getAbout(): ?string
    {
        return $this->about;
    }

    /**
     * @param string|null $about
     *
     * @return $this
     */
    public function setAbout(?string $about): self
    {
        $this->about = $about;

        return $this;
    }
}
