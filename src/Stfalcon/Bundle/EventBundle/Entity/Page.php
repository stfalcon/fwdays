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
class Page extends BasePage
{
    /**
     * @var bool $showInMenu
     *
     * @ORM\Column(name="show_in_menu", type="boolean")
     */
    protected $showInMenu = false;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    protected $event;

    /**
     * @var int $sortOrder
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=false)
     */
    protected $sortOrder = 1;

    /**
     * @param Event $event
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param bool $showInMenu
     */
    public function setShowInMenu($showInMenu)
    {
        $this->showInMenu = $showInMenu;
    }

    /**
     * @return bool
     */
    public function isShowInMenu()
    {
        return $this->showInMenu;
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
}
