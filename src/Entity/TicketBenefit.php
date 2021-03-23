<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\Translatable\TranslatableInterface;
use App\Traits\TranslateTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Table(name="event__ticket_benefits")
 * @ORM\Entity()
 *
 * @Gedmo\TranslationEntity(class="App\Entity\Translation\TicketBenefitTranslation")
 *
 * @UniqueEntity({"type", "event"}, errorPath="type")
 *
 * @Vich\Uploadable
 */
class TicketBenefit implements TranslatableInterface
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
     * @var File|null
     *
     * @Assert\File(
     *     maxSize="1M",
     *     mimeTypes={"application/pdf"}
     * )
     * @Vich\UploadableField(mapping="event_certificate", fileNameProperty="certificate")
     */
    private $certificateFile;

    /**
     * @var string|null
     *
     * @ORM\Column(name="certificate", type="string", length=255, nullable=true)
     *
     * @Gedmo\Translatable(fallback=true)
     */
    private $certificate;

    /**
     * @var \DateTime|null
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @ORM\Column(type="datetime", name="updated_at", nullable=true)
     */
    private $updatedAt;

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

    /**
     * @return File|null
     */
    public function getCertificateFile(): ?File
    {
        return $this->certificateFile;
    }

    /**
     * @param File $certificateFile
     *
     * @return $this
     */
    public function setCertificateFile(File $certificateFile): self
    {
        $this->certificateFile = $certificateFile;
        $this->setUpdatedAt(new \DateTime());

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCertificate(): ?string
    {
        return $this->certificate;
    }

    /**
     * @param string|null $certificate
     *
     * @return $this
     */
    public function setCertificate(?string $certificate): self
    {
        $this->certificate = $certificate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}
