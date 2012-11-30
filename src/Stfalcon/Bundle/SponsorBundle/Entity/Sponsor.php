<?php

namespace Stfalcon\Bundle\SponsorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

use Stfalcon\Bundle\SponsorBundle\Entity\EventSponsor;

/**
 * Stfalcon\Bundle\SponsorBundle\Entity\Sponsor
 *
 * @Vich\Uploadable
 * @ORM\Table(name="sponsors")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\SponsorBundle\Repository\SponsorRepository")
 */
class Sponsor
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
     * @var string $slug
     *
     * @ORM\Column(name="slug", type="string", length=255)
     */
    protected $slug;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string $site
     *
     * @ORM\Column(name="site", type="string", nullable=true, length=255)
     * @Assert\Url
     */
    protected $site;

    /**
     * @var string $logo
     *
     * @ORM\Column(name="logo", type="string", nullable=true, length=255)
     */
    protected $logo;

    /**
     * @var int $sortOrder
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=false)
     */
    protected $sortOrder = 1;

    /**
     * @var resource $file
     *
     * @Assert\File(maxSize="6000000")
     * @Assert\Image
     *
     * @Vich\UploadableField(mapping="sponsor_image", fileNameProperty="logo")
     */
    protected $file;

    /**
     * @var string $about
     *
     * @ORM\Column(name="about", type="text", nullable=true)
     */
    protected $about;

    /**
     * @ORM\OneToMany(targetEntity="Stfalcon\Bundle\SponsorBundle\Entity\EventSponsor",
     *     mappedBy="sponsor", cascade={"persist", "remove"}, orphanRemoval=true
     * )
     */
    protected $sponsorEvents;

    /**
     * @var \DateTime $created
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @var \DateTime $updated
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;


    /**
     * @var boolean onMain
     *
     * @ORM\Column(name="on_main", type="boolean")
     */
    protected $onMain = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sponsorEvents = new ArrayCollection();
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
     * Get logo filename
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set logo filename
     *
     * @param string $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    /**
     * Set sortOrder
     *
     * @param int $sortOrder
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * Get sortOrder
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set site
     *
     * @param string $site
     */
    public function setSite($site)
    {
        $this->site = $site;
    }

    /**
     * Get site
     *
     * @return string
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @return resource
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Set about
     *
     * @param string $about
     */
    public function setAbout($about)
    {
        $this->about = $about;
    }

    /**
     * Get about
     *
     * @return string
     */
    public function getAbout()
    {
        return $this->about;
    }


    /**
     * @param EventSponsor $sponsorEvent
     */
    public function addSponsorEvents(EventSponsor $sponsorEvent)
    {
        $this->sponsorEvents[] = $sponsorEvent;
    }

    /**
     * @param $sponsorEvents
     */
    public function setSponsorEvents($sponsorEvents)
    {
        foreach($sponsorEvents as $sponsorEvent){
            $sponsorEvent->setSponsor($this);
        }
        $this->sponsorEvents = $sponsorEvents;
    }

    /**
     * @return ArrayCollection
     */
    public function getSponsorEvents()
    {
        return $this->sponsorEvents;
    }

    /**
     * Get createdAt
     *
     * @return \Datetime createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt
     *
     * @param \Datetime $createdAt createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get updatedAt
     *
     * @return \Datetime updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedAt
     *
     * @param \Datetime $updatedAt updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get sponsor name if object treated like a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }


    /**
     * @param boolean $onMain
     */
    public function setOnMain($onMain)
    {
        $this->onMain = $onMain;
    }

    /**
     * @return boolean
     */
    public function getOnMain()
    {
        return $this->onMain;
    }
}
