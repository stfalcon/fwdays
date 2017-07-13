<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Stfalcon\Bundle\EventBundle\Traits\Translate;
use Symfony\Component\Validator\Constraints as Assert;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Event
 *
 * @ORM\Table(name="event__news")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\NewsRepository")
 * @Gedmo\TranslationEntity(class="Stfalcon\Bundle\EventBundle\Entity\Translation\NewsTranslation")
 */
class News implements Translatable
{
    use Translate;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Stfalcon\Bundle\EventBundle\Entity\Translation\NewsTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;
    /**
     * @var string $title
     * @Gedmo\Translatable(fallback=true)
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var text $preview
     * @Gedmo\Translatable(fallback=true)
     * @ORM\Column(name="preview", type="text")
     */
    private $preview;

    /**
     * @var text $text
     * @Gedmo\Translatable(fallback=true)
     * @ORM\Column(name="text", type="text")
     */
    private $text;
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $slug
     *
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private $slug;

    /**
     * @var datetime $created_at
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created_at;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set preview
     *
     * @param text $preview
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;
    }

    /**
     * Get preview
     *
     * @return text
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * Set text
     *
     * @param text $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Get text
     *
     * @return text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set created_at
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    }

    /**
     * Get created_at
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }
    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }
}
