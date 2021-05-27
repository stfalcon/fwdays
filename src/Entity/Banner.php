<?php

namespace App\Entity;

use App\Entity\Timestampable\TimestampableInterface;
use App\Entity\Timestampable\TimestampableTrait;
use App\Traits\TranslateTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="banners",
 *      indexes={
 *         @ORM\Index(columns={"since", "till", "active"})
 *     })
 * @ORM\Entity()
 *
 * @Gedmo\TranslationEntity(class="App\Entity\Translation\BannerTranslation")
 */
class Banner implements TimestampableInterface
{
    use TimestampableTrait;
    use TranslateTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(
     *   targetEntity="App\Entity\Translation\BannerTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $since = null;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\Expression("null === value || value > this.getSince()", message="till должна быть больше чем since")
     */
    private $till = null;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     *
     * @Assert\NotBlank()
     *
     * @Gedmo\Translatable(fallback=true)
     */
    private $text;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false, name="background_color")
     *
     * @Assert\NotBlank()
     */
    private $backgroundColor;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     *
     * @Assert\NotBlank()
     */
    private $url = '/';

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $active = true;

    /**
     * Banner constructor.
     */
    public function __construct()
    {
        $this->initTimestampableFields();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getSince(): ?\DateTimeInterface
    {
        return $this->since;
    }

    /**
     * @param \DateTimeInterface|null $since
     *
     * @return $this
     */
    public function setSince(?\DateTimeInterface $since): self
    {
        $this->since = $since;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getTill(): ?\DateTimeInterface
    {
        return $this->till;
    }

    /**
     * @param \DateTimeInterface|null $till
     *
     * @return $this
     */
    public function setTill(?\DateTimeInterface $till): self
    {
        $this->till = $till;

        return $this;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return $this
     */
    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getBackgroundColor(): string
    {
        return $this->backgroundColor;
    }

    /**
     * @param string $backgroundColor
     *
     * @return $this
     */
    public function setBackgroundColor(string $backgroundColor): self
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return $this
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }
}
