<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\TranslateTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="event__ticket_benefits")
 * @ORM\Entity()
 *
 * @Gedmo\TranslationEntity(class="App\Entity\Translation\TicketBenefitTranslation")
 *
 * @UniqueEntity({"type", "event"}, errorPath="type")
 */
class TicketBenefit
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
     * @ORM\OneToMany(
     *   targetEntity="App\Entity\Translation\TicketBenefitTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="ticketBenefits")
     */
    private $event;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=false)
     *
     * @Assert\NotBlank()
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="benefits")
     *
     * @Gedmo\Translatable(fallback=true)
     *
     * @Assert\NotBlank()
     */
    private $benefits;

    /**
     * TicketBenefit constructor.
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @param Event $event
     *
     * @return $this
     */
    public function setEvent(Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBenefits(): ?string
    {
        return $this->benefits;
    }

    /**
     * @param string $benefits
     *
     * @return $this
     */
    public function setBenefits(string $benefits): self
    {
        $this->benefits = $benefits;

        return $this;
    }
}
