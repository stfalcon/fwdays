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
     * @var boolean $showInMenu
     *
     * @ORM\Column(name="show_in_menu", type="boolean")
     */
    private $showInMenu = false;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;

    public function setEvent(Event $event) {
        $this->event = $event;
    }

    public function getEvent() {
        return $this->event;
    }

    public function setShowInMenu($showInMenu) {
        $this->showInMenu = $showInMenu;
    }

    public function isShowInMenu() {
        return $this->showInMenu;
    }

}
