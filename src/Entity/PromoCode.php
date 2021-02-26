<?php

namespace App\Entity;

use App\Model\Blameable\BlameableInterface;
use App\Model\Blameable\BlameableTrait;
use App\Model\Translatable\TranslatableInterface;
use App\Traits\TranslateTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PromoCode.
 *
 * @ORM\Table(name="event__promo_code")
 * @ORM\Entity(repositoryClass="App\Repository\PromoCodeRepository")
 * @ORM\EntityListeners({
 *     "App\EventListener\ORM\Blameable\BlameableListener"
 * })
 *
 * @UniqueEntity(
 *     "code",
 *     errorPath="code",
 *     message="Поле code повинне бути унікальне."
 * )
 * @Gedmo\TranslationEntity(class="App\Entity\Translation\PromoCodeTranslation")
 */
class PromoCode implements TranslatableInterface, BlameableInterface
{
    use TranslateTrait;
    use BlameableTrait;

    public const PROMOCODE_APPLIED = 'promocode_applied';
    public const PROMOCODE_LOW_THAN_DISCOUNT = 'error.promocode.low_than_discount';
    public const PROMOCODE_USED = 'error.promocode.used';
    public const PROMOCODE_NOT_FOUND = 'error.promocode.not_found';
    public const PROMOCODE_OTHER_TYPE = 'error.promocode.other_type';

    /**
     * @ORM\OneToMany(
     *   targetEntity="App\Entity\Translation\PromoCodeTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Gedmo\Translatable(fallback=true)
     *
     * @Assert\NotBlank()
     */
    protected $title = '';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     *
     * @Assert\Range(min="0", max="100")
     *
     * @Groups("payment.view")
     */
    protected $discountAmount;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     *
     * @Groups("payment.view")
     */
    protected $code;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $event;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $endDate;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"default":0})
     *
     * @Assert\GreaterThanOrEqual(0)
     */
    protected $usedCount = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"default":0})
     *
     * @Assert\GreaterThanOrEqual(0)
     */
    protected $maxUseCount = 0;

    /**
     * @var int
     */
    protected $tmpUsedCount = 0;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="created_by")
     */
    private $createdBy;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ticker_cost_type",  type="string", nullable=true, length=20)
     */
    private $tickerCostType;

    /**
     * PromoCode constructor.
     */
    public function __construct()
    {
        $this->code = substr(strtoupper(md5(uniqid())), 0, 10);
        $this->discountAmount = 10;
        $this->endDate = new \DateTime('+10 days');
        $this->translations = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getUsedCount()
    {
        return $this->usedCount;
    }

    /**
     * @param int $usedCount
     *
     * @return $this
     */
    public function setUsedCount($usedCount): self
    {
        $this->usedCount = $usedCount;

        return $this;
    }

    /**
     * @return $this
     */
    public function incUsedCount(): self
    {
        ++$this->usedCount;

        return $this;
    }

    /**
     * @param string $code
     *
     * @return $this
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param int $discountAmount
     *
     * @return $this
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->discountAmount = $discountAmount;

        return $this;
    }

    /**
     * @return int
     */
    public function getDiscountAmount(): int
    {
        return $this->discountAmount;
    }

    /**
     * @param \DateTime $endDate
     *
     * @return $this
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \App\Entity\Event $event
     *
     * @return $this
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return \App\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->title.' - '.$this->discountAmount.'%'.' ('.$this->code.')';
    }

    /**
     * @return int
     */
    public function getMaxUseCount()
    {
        return $this->maxUseCount;
    }

    /**
     * @param int $maxUseCount
     *
     * @return $this
     */
    public function setMaxUseCount($maxUseCount)
    {
        $this->maxUseCount = $maxUseCount;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUnlimited()
    {
        $unlimited = 0 === $this->maxUseCount;

        return $unlimited;
    }

    /**
     * @return int|string
     */
    public function getUsed()
    {
        if ($this->isUnlimited()) {
            return $this->getUsedCount();
        }

        return $this->getUsedCount().' из '.$this->getMaxUseCount();
    }

    /**
     * @return bool
     */
    public function isCanBeUsed()
    {
        return $this->isUnlimited() || $this->getUsedCount() < $this->getMaxUseCount();
    }

    /**
     * @return $this
     */
    public function incTmpUsedCount()
    {
        ++$this->tmpUsedCount;

        return $this;
    }

    /**
     * @return $this
     */
    public function clearTmpUsedCount(): self
    {
        $this->tmpUsedCount = 0;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCanBeTmpUsed()
    {
        return $this->isUnlimited() || ($this->getUsedCount() + $this->tmpUsedCount) < $this->getMaxUseCount();
    }

    /**
     * @param PromoCode|null $promoCode
     *
     * @return bool
     */
    public function isEqualTo(?PromoCode $promoCode): bool
    {
        if (!$promoCode instanceof self) {
            return false;
        }

        if ($promoCode->getId() !== $this->getId()) {
            return false;
        }

        return true;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return $this
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTickerCostType(): ?string
    {
        return $this->tickerCostType;
    }

    /**
     * @param string|null $tickerCostType
     *
     * @return $this
     */
    public function setTickerCostType(?string $tickerCostType): self
    {
        $this->tickerCostType = $tickerCostType;

        return $this;
    }

    /**
     * @param string|null $ticketCostType
     *
     * @return bool
     */
    public function isSameTicketCostTypeOrNull(?string $ticketCostType = null): bool
    {
        return $this->tickerCostType === $ticketCostType;
    }
}
