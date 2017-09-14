<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Stfalcon\Bundle\EventBundle\Entity\AbstractClass\AbstractPage;
use Stfalcon\Bundle\EventBundle\Traits\Translate;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Event
 *
 * @ORM\Table(name="event__pages")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\EventPageRepository")
 * @Gedmo\TranslationEntity(class="Stfalcon\Bundle\EventBundle\Entity\Translation\EventPageTranslation")
 */
class EventPage extends AbstractPage implements Translatable
{
    use Translate;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Stfalcon\Bundle\EventBundle\Entity\Translation\EventPageTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

    /**
     * @var bool $showInMenu
     *
     * @ORM\Column(name="show_in_menu", type="boolean")
     */
    protected $showInMenu = false;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="pages")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    protected $event;

    /**
     * @var int $sortOrder
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=false)
     */
    protected $sortOrder = 1;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

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
