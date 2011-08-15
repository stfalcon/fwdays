<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Stfalcon\Bundle\NewsBundle\Entity\BaseNews;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Event
 *
 * @ORM\Table(name="event__news")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Entity\NewsRepository")
 */
class News extends BaseNews {
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;
    
    public function getEvent() {
        return $this->event;
    }

    public function setEvent($event) {
        $this->event = $event;
    }

}
