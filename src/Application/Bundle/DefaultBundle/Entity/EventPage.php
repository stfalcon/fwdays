<?php

namespace Application\Bundle\DefaultBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Application\Bundle\DefaultBundle\Entity\AbstractClass\AbstractPage;
use Application\Bundle\DefaultBundle\Traits\TranslateTrait;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Application\Bundle\DefaultBundle\Entity\Event.
 *
 * @ORM\Table(name="event__pages")
 * @ORM\Entity(repositoryClass="Application\Bundle\DefaultBundle\Repository\EventPageRepository")
 *
 * @Gedmo\TranslationEntity(class="Application\Bundle\DefaultBundle\Entity\Translation\EventPageTranslation")
 */
class EventPage extends AbstractPage implements Translatable
{
    use TranslateTrait;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Application\Bundle\DefaultBundle\Entity\Translation\EventPageTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}, fetch="EXTRA_LAZY"
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
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="pages", fetch="EXTRA_LAZY")
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
