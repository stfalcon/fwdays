<?php

namespace App\Entity;

use App\Model\Translatable\TranslatableInterface;
use App\Traits\TranslateTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="city")
 * @ORM\Entity()
 *
 * @Gedmo\TranslationEntity(class="App\Entity\Translation\CityTranslation")
 */
class City implements TranslatableInterface
{
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false, unique=true)
     *
     * @Assert\NotNull()
     * @Assert\NotBlank()
     * @Assert\Length(min="3")
     *
     * @Gedmo\Translatable(fallback=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="url_name", type="string", nullable=false, unique=true)
     *
     * @Assert\NotNull()
     * @Assert\NotBlank()
     * @Assert\Length(min="3")
     */
    private $urlName;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="default_city", type="boolean", nullable=true)
     */
    private $default = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active = true;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", name="contact_info", nullable=true)
     */
    private $contactInfo;

    /**
     * @ORM\OneToMany(
     *   targetEntity="App\Entity\Translation\CityTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrlName(): string
    {
        return $this->urlName;
    }

    /**
     * @param string $urlName
     *
     * @return $this
     */
    public function setUrlName(string $urlName): self
    {
        $this->urlName = $urlName;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isDefault(): ?bool
    {
        return $this->default;
    }

    /**
     * @param bool|null $default
     *
     * @return $this
     */
    public function setDefault(?bool $default): self
    {
        $this->default = $default;

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

    /**
     * @return string|null
     */
    public function getContactInfo(): ?string
    {
        return $this->contactInfo;
    }

    /**
     * @param string|null $contactInfo
     *
     * @return $this
     */
    public function setContactInfo(?string $contactInfo): self
    {
        $this->contactInfo = $contactInfo;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
