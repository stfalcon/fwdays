<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventSponsor.
 *
 * @ORM\Table(name="event__events_sponsors")
 * @ORM\Entity(repositoryClass="App\Repository\SponsorRepository")
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
     * @var Sponsor
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Sponsor", inversedBy="sponsorEvents")
     * @ORM\JoinColumn(name="sponsor_id", referencedColumnName="id")
     */
    protected $sponsor;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    protected $event;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Category")
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
     * @param Category|null $category
     *
     * @return $this
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return \App\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Event|null $event
     *
     * @return $this
     */
    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return \App\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Sponsor|null $sponsor
     *
     * @return $this
     */
    public function setSponsor(?Sponsor $sponsor): self
    {
        $this->sponsor = $sponsor;

        return $this;
    }

    /**
     * @return \App\Entity\Sponsor
     */
    public function getSponsor()
    {
        return $this->sponsor;
    }
}
