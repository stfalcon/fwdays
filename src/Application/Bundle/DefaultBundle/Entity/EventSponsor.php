<?php

namespace Application\Bundle\DefaultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Application\Bundle\DefaultBundle\Entity\Event;

/**
 * Application\Bundle\DefaultBundle\Entity\EventSponsor.
 *
 * @ORM\Table(name="event__events_sponsors")
 * @ORM\Entity(repositoryClass="Application\Bundle\DefaultBundle\Repository\SponsorRepository")
 */
class EventSponsor
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \Application\Bundle\DefaultBundle\Entity\Sponsor
     *
     * @ORM\ManyToOne(targetEntity="Application\Bundle\DefaultBundle\Entity\Sponsor", inversedBy="sponsorEvents")
     * @ORM\JoinColumn(name="sponsor_id", referencedColumnName="id")
     */
    protected $sponsor;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Application\Bundle\DefaultBundle\Entity\Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    protected $event;

    /**
     * @var \Application\Bundle\DefaultBundle\Entity\Category
     *
     * @ORM\ManyToOne(targetEntity="Application\Bundle\DefaultBundle\Entity\Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;

    /**
     * Get title.
     *
     * @return string
     */
    public function __toString()
    {
        $title = (string) $this->getEvent()->getName() ?: '-';

        if ($this->getCategory() instanceof Category) {
            $title .= ' / '.$this->getCategory()->getName();
        }

        return $title;
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
     * @param \Application\Bundle\DefaultBundle\Entity\Category $category
     *
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return \Application\Bundle\DefaultBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param \Application\Bundle\DefaultBundle\Entity\Event $event
     *
     * @return $this
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return \Application\Bundle\DefaultBundle\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param \Application\Bundle\DefaultBundle\Entity\Sponsor $sponsor
     *
     * @return $this
     */
    public function setSponsor($sponsor)
    {
        $this->sponsor = $sponsor;

        return $this;
    }

    /**
     * @return \Application\Bundle\DefaultBundle\Entity\Sponsor
     */
    public function getSponsor()
    {
        return $this->sponsor;
    }
}
