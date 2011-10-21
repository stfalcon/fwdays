<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Stfalcon\Bundle\PageBundle\Entity\BasePage;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Event
 *
 * @ORM\Table(name="event__pages")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\PageRepository")
 */
class Page extends BasePage {
    
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
