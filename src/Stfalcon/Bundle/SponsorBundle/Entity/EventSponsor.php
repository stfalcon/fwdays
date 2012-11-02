<?php

namespace Stfalcon\Bundle\SponsorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Stfalcon\Bundle\SponsorBundle\Entity\Category;

/**
 * Stfalcon\Bundle\SponsorBundle\Entity\EventSponsor
 *
 * @ORM\Table(name="event__events_sponsors")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\SponsorBundle\Repository\SponsorRepository")
 *
 */
class EventSponsor
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \Stfalcon\Bundle\SponsorBundle\Entity\Sponsor
     *
     * @ORM\ManyToOne(targetEntity="Stfalcon\Bundle\SponsorBundle\Entity\Sponsor")
     * @ORM\JoinColumn(name="sponsor_id", referencedColumnName="id")
     */
    protected $sponsor;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Stfalcon\Bundle\EventBundle\Entity\Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    protected $event;

    /**
     * @var \Stfalcon\Bundle\SponsorBundle\Entity\Category
     *
     * @ORM\ManyToOne(targetEntity="Stfalcon\Bundle\SponsorBundle\Entity\Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;

    /**
     * Get title
     */
    public function __toString()
    {
        $title = $this->getEvent()->getName();

        if($this->getCategory() instanceof Category){
            $title .=  ' / ' . $this->getCategory()->getName();
        }

        return $title;
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
     * @param \Stfalcon\Bundle\SponsorBundle\Entity\Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return \Stfalcon\Bundle\SponsorBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param \Stfalcon\Bundle\EventBundle\Entity\Event $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return \Stfalcon\Bundle\EventBundle\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param \Stfalcon\Bundle\SponsorBundle\Entity\Sponsor $sponsor
     */
    public function setSponsor($sponsor)
    {
        $this->sponsor = $sponsor;
    }

    /**
     * @return \Stfalcon\Bundle\SponsorBundle\Entity\Sponsor
     */
    public function getSponsor()
    {
        return $this->sponsor;
    }
}