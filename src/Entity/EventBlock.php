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

    const YOUTUBE_PRIVATE_VIDEO = 'youtube_private_video';
    const YOUTUBE_PRIVATE_PLAYLIST = 'youtube_private_playlist';
    const VIMEO_PRIVATE_VIDEO = 'vimeo_private_video';
    const VIMEO_PRIVATE_PLAYLIST = 'vimeo_private_playlist';

    const YOUTUBE_PRIVATE_VIDEO_STANDARD = 'youtube_private_video_standard';
    const YOUTUBE_PRIVATE_PLAYLIST_STANDARD = 'youtube_private_playlist_standard';
    const VIMEO_PRIVATE_VIDEO_STANDARD = 'vimeo_private_video_standard';
    const VIMEO_PRIVATE_PLAYLIST_STANDARD = 'vimeo_private_playlist_standard';

    const YOUTUBE_PRIVATE_VIDEO_PREMIUM = 'youtube_private_video_premium';
    const YOUTUBE_PRIVATE_PLAYLIST_PREMIUM = 'youtube_private_playlist_premium';
    const VIMEO_PRIVATE_VIDEO_PREMIUM = 'vimeo_private_video_premium';
    const VIMEO_PRIVATE_PLAYLIST_PREMIUM = 'vimeo_private_playlist_premium';

    public const VISIBILITY_ALL = 'vis_all';
    public const VISIBILITY_NOT_AUTH = 'vis_not_auth';
    public const VISIBILITY_AUTH = 'vis_auth';
    public const VISIBILITY_EVENT_REGISTERED = 'vis_event_registered';
    public const VISIBILITY_EVENT_STANDARD = 'vis_event_standard';
    public const VISIBILITY_EVENT_PREMIUM = 'vis_event_premium';

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
     * @var string
     *
     * @ORM\Column(type="string", name="visibility", options={"default": "vis_all"}, nullable=false)
     *
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    private $visibility = self::VISIBILITY_ALL;

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
     * @return string
     */
    public function getVisibility(): string
    {
        return $this->visibility;
    }

    /**
     * @param string $visibility
     *
     * @return $this
     */
    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * @return array
     */
    public static function getVisibilityChoices(): array
    {
        return [
            'Вид авторизации' => [
                'Всем (видят все)' => self::VISIBILITY_ALL,
                'Не авторизирован (видят только не авторизированные пользователи)' => self::VISIBILITY_NOT_AUTH,
                'Авторизирован (видят только авторизированные пользователи)' => self::VISIBILITY_AUTH,
            ],
            'Билет' => [
                'Регистрация на событие (видят только бесплатно зарегистрированые на событие)' => self::VISIBILITY_EVENT_REGISTERED,
                'Standard билет (видят только купившие билет Standard)' => self::VISIBILITY_EVENT_STANDARD,
                'Premium билет(видят только купившие билет Premium)' => self::VISIBILITY_EVENT_PREMIUM,
            ],
        ];
    }

    /**
     * @return array
     */
    public static function getTypeChoices(): array
    {
        return [
            'общие' => [
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
            ],
            'vimeo' => [
                'vimeo трансляция' => self::VIMEO_PRIVATE_VIDEO,
                'vimeo playlist' => self::VIMEO_PRIVATE_PLAYLIST,
                '(не использовать) vimeo трансляция standard' => self::VIMEO_PRIVATE_VIDEO_STANDARD,
                '(не использовать) vimeo playlist standard' => self::VIMEO_PRIVATE_PLAYLIST_STANDARD,
                '(не использовать) vimeo трансляция premium' => self::VIMEO_PRIVATE_VIDEO_PREMIUM,
                '(не использовать) vimeo playlist premium' => self::VIMEO_PRIVATE_PLAYLIST_PREMIUM,
            ],
            'youtube' => [
                'youtube трансляция' => self::YOUTUBE_PRIVATE_VIDEO,
                'youtube playlist' => self::YOUTUBE_PRIVATE_PLAYLIST,
                '(не использовать) youtube трансляция standard' => self::YOUTUBE_PRIVATE_VIDEO_STANDARD,
                '(не использовать) youtube playlist standard' => self::YOUTUBE_PRIVATE_PLAYLIST_STANDARD,
                '(не использовать) youtube трансляция premium' => self::YOUTUBE_PRIVATE_VIDEO_PREMIUM,
                '(не использовать) youtube playlist premium' => self::YOUTUBE_PRIVATE_PLAYLIST_PREMIUM,
            ],
        ];
    }
}
