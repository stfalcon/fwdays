<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Stfalcon\Bundle\PageBundle\Entity\BasePage;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Review
 *
 * @ORM\Table(name="event__reviews")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Entity\ReviewRepository")
 */
class Review extends BasePage {
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Speaker")
     * @ORM\JoinTable(name="event__speakers_reviews",
     *   joinColumns={
     *     @ORM\JoinColumn(name="review_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="speaker_id", referencedColumnName="id")
     *   }
     * )
     */  
    private $speakers;
    
    public function __construct()
    {
        $this->speakers = new ArrayCollection();
    }
    
    public function getEvent() {
        return $this->event;
    }

    public function setEvent($event) {
        $this->event = $event;
    }
    
    public function getSpeakers() {
        return $this->speakers;
    }

    public function setSpeaker($speakers) {
        $this->speakers = $speakers;
    }

}