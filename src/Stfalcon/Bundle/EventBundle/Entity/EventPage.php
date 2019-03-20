<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Stfalcon\Bundle\EventBundle\Entity\AbstractClass\AbstractPage;
use Stfalcon\Bundle\EventBundle\Traits\TranslateTrait;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Event.
 *
 * @ORM\Table(name="event__pages")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\EventPageRepository")
 *
 * @Gedmo\TranslationEntity(class="Stfalcon\Bundle\EventBundle\Entity\Translation\EventPageTranslation")
 */
class EventPage extends AbstractPage implements Translatable
{
    use TranslateTrait;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Stfalcon\Bundle\EventBundle\Entity\Translation\EventPageTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

    /**
     * @var bool
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
     * @var string
     *
     * @Gedmo\Translatable(fallback=true)
     *
     * @ORM\Column(name="text_new", type="text", nullable=true)
     */
    protected $textNew;

    /**
     * @var int
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=false)
     */
    protected $sortOrder = 1;

    /**
     * @return string
     */
    public function getTextNew()
    {
        return $this->textNew;
    }

    /**
     * @param string $textNew
     *
     * @return $this
     */
    public function setTextNew($textNew)
    {
        $this->textNew = $textNew;

        return $this;
    }

    /**
     * EventPage constructor.
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * @param Event $event
     *
     * @return $this
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;

        return $this;
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
     *
     * @return $this
     */
    public function setShowInMenu($showInMenu)
    {
        $this->showInMenu = $showInMenu;

        return $this;
    }

    /**
     * @return bool
     */
    public function isShowInMenu()
    {
        return $this->showInMenu;
    }

    /**
     * Set sortOrder.
     *
     * @param int $sortOrder
     *
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * Get sortOrder.
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }
}
