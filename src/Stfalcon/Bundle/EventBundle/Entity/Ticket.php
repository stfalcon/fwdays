<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Application\Bundle\UserBundle\Entity\User;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Ticket
 *
 * @ORM\Table(name="event__tickets")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\TicketRepository")
 */
class Ticket
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Сумма для оплаты
     *
     * @var float $amount
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2)
     */
    //@todo переименовать в price
    private $amount;

    /**
     * Сумма без учета скидки
     *
     * @var float $amountWithoutDiscount
     *
     * @ORM\Column(name="amount_without_discount", type="decimal", precision=10, scale=2)
     */
    //@todo переименовать в total
    private $amountWithoutDiscount;

    //@todo добавить свойство discount?

    /**
     * @var PromoCode
     *
     * @ORM\ManyToOne(targetEntity="PromoCode")
     * @ORM\JoinColumn(name="promo_code_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $promoCode;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $event;

    /**
     * На кого выписан билет. Т.е. участник не обязательно плательщик
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Application\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var \Stfalcon\Bundle\EventBundle\Entity\Payment
     *
     * @ORM\ManyToOne(targetEntity="Stfalcon\Bundle\EventBundle\Entity\Payment", inversedBy="tickets")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $payment;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @var boolean $used
     * @ORM\Column(name="used", type="boolean")
     */
    private $used = false;

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
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     *
     * @return void
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @return bool
     */
    public function hasPayment()
    {
        return (bool) $this->getPayment();
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param Payment|null $payment
     *
     * @return void
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Checking if ticket is "paid"
     *
     * @return bool
     */
    public function isPaid()
    {
        return (bool) ($this->hasPayment() && $this->getPayment()->isPaid());
    }

    /**
     * Mark ticket as "used"
     *
     * @param bool $used
     */
    public function setUsed($used)
    {
        $this->used = $used;
    }

    /**
     * Checking if ticket is "used"
     *
     * @return bool
     */
    public function isUsed()
    {
        return $this->used;
    }

    /**
     * Generate unique md5 hash for ticket
     *
     * @return string
     */
    public function getHash()
    {
        return md5($this->getId() . $this->getCreatedAt()->format('Y-m-d H:i:s'));
    }

    public function __toString()
    {
        return (string) $this->getId() . ' (' . $this->getUser()->getFullname() . ')';
    }

    /**
     * @param float $amount
     */
    private function setAmount($amount)
    {
        // @todo wtf? в коментах нижче
        // мы можем устанавливать/обновлять стоимость только для билетов
        // с неоплаченными платежами
//        if ($this->hasPayment() && $this->getPayment()->isPending()) {
            $this->amount = $amount;
//            $this->getPayment()->recalculateAmount();
//        }
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $cost
     */
    public function setCost($cost)
    {
        $this->amountWithoutDiscount = $cost;
    }

    /**
     * @return float
     */
    public function getCost()
    {
        return $this->amountWithoutDiscount;
    }

    /**
     * @param \Stfalcon\Bundle\EventBundle\Entity\PromoCode $promoCode
     */
    public function setPromoCode($promoCode)
    {
        $this->setHasDiscount(true);
        $amountWithDiscount = $this->amountWithoutDiscount - ($this->amountWithoutDiscount * ($promoCode->getDiscountAmount() / 100));
        $this->setAmount($amountWithDiscount);
        $this->promoCode = $promoCode;
    }

    /**
     * @return \Stfalcon\Bundle\EventBundle\Entity\PromoCode
     */
    public function getPromoCode()
    {
        return $this->promoCode;
    }

    /**
     * @return bool
     */
    public function hasPromoCode()
    {
        return !empty($this->promoCode);
    }

    /**
     * @return boolean
     */
    public function getHasDiscount()
    {
        // @todo назва методу якась не ок і нафіга він тут взагалі потрібен?
        return (boolean) $this->getAmountWithoutDiscount() == $this->getAmount();
    }

    /**
     * @return string
     */
    public function generatePdfFilename()
    {
        return 'ticket-' . $this->getEvent()->getSlug() . '.pdf';
    }

    /**
     * Apply discount for ticket price
     *
     * @param type $discountPercents
     */
    public function applyDiscount($discountPercents) {
        $discount = round($this->getCost() / 100 * $discountPercents, 2);
        $this->setAmount($this->getCost() - $discount);
        $this->setHasDiscount(true);
    }

    /**
     * Get discount amount in percents
     *
     * @return int
     */
    public function getDiscountAsPercents() {
        return (int) (100 - ($this->getAmount() * 100 / $this->getCost()));
    }

    // удаляем
    // @todo переименовать методы и переменные.
    // amount => сумма, величина. цена => price, cost
    public function setAmountWithoutDiscount($amountWithoutDiscount)
    {
        $this->amountWithoutDiscount = $amountWithoutDiscount;
    }    public function getAmountWithoutDiscount()
    {
        return $this->amountWithoutDiscount;
    }
    /**
     * Set amount with discount.
     * If has promo code use discount with promo code
     *
     * @param $discount
     */
    // @todo видалити цей метод. він просто дублює логіку з сервісу і методу applyDiscount
    // тільки по свому
    public function setAmountWithDiscount($discount) {
        $price = $this->getAmountWithoutDiscount();

        if ($promoCode = $this->getPromoCode()) {
            $cost = $price - ($price * ($promoCode->getDiscountAmount() / 100));
        } else {
            $cost = $price - ($price * 100 / $discount);
        }

        $this->setAmount($cost);
    }
    /**
     * @param boolean $hasDiscount
     */
    // @todo цей метод і властивість зайві. я можу просто дивитись чи сума зізнижкою рівна сумі без знижки
    public function setHasDiscount($hasDiscount)
    {
        $this->hasDiscount = $hasDiscount;
    }
    /**
     * @var bool
     *
     * @ORM\Column(name="has_discount", type="boolean")
     */
    // @todo цей метод і властивість зайві. я можу просто дивитись чи сума зізнижкою рівна сумі без знижки
    private $hasDiscount = false;


}
