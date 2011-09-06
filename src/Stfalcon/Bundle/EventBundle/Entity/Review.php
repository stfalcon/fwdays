<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Stfalcon\Bundle\PageBundle\Entity\BasePage;
use Doctrine\ORM\Mapping as ORM;
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
     * @ORM\ManyToOne(targetEntity="Speaker")
     * @ORM\JoinColumn(name="speaker_id", referencedColumnName="id")
     */
    private $speaker;
    
    public function getEvent() {
        return $this->event;
    }

    public function setEvent($event) {
        $this->event = $event;
    }
    
    public function getSpeaker() {
        return $this->speaker;
    }

    public function setSpeaker($speaker) {
        $this->speaker = $speaker;
    }

}