<?php

namespace App\Entity;

use App\Model\Translatable\TranslatableInterface;
use App\Traits\TranslateTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * EventBlock.
 *
 * @ORM\Table(name="event_block")
 * @ORM\Entity()
 *
 * @Gedmo\TranslationEntity(class="App\Entity\Translation\BlockTranslation")
 */
class EventBlock implements TranslatableInterface
{
    use TranslateTrait;

    const HTML_TEXT = 'text';
    const PROGRAM = 'program';
    const PARTNERS = 'partners';
    const PRICES = 'prices';
    const DESCRIPTION = 'description';
    const VENUE = 'venue';
    const SPEAKERS = 'speakers';
    const REVIEWS = 'reviews';
    const CANDIDATE_SPEAKERS = 'candidate_speakers';
    const COMMITTEE_SPEAKERS = 'committee_speakers';
    const EXPERT_SPEAKERS = 'expert_speakers';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="blocks")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="cascade")
     */
    private $event;

    /**
     * @ORM\OneToMany(
     *   targetEntity="App\Entity\Translation\BlockTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     *
     * @Assert\NotBlank()
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Gedmo\Translatable(fallback=true)
     *
     * @Assert\Expression(
     *     "(value !== null and value !== '') or this.getType() !== 'text'",
     *     message="введите текст."
     * )
     */
    private $text;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $visible = true;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer")
     *
     * @Assert\NotBlank()
     * @Assert\GreaterThan(value="0")
     */
    private $position;

    /**
     * EventBlock constructor.
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     *
     * @return $this
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     *
     * @return $this
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     *
     * @return $this
     */
    public function setPosition($position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return array
     */
    public static function getTypeChoices(): array
    {
        return [
            'html текст' => self::HTML_TEXT,
            'програма' => self::PROGRAM,
            'партнеры' => self::PARTNERS,
            'цены' => self::PRICES,
            'описание' => self::DESCRIPTION,
            'карта' => self::VENUE,
            'докладчики' => self::SPEAKERS,
            'доклады' => self::REVIEWS,
            'кандидаты' => self::CANDIDATE_SPEAKERS,
            'програмный комитет' => self::COMMITTEE_SPEAKERS,
            'эксперты' => self::EXPERT_SPEAKERS,
        ];
    }
}
