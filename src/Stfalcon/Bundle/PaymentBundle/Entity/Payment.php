<?php

namespace Stfalcon\Bundle\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Application\Bundle\UserBundle\Entity\User;

/**
 * Stfalcon\Bundle\PaymentBundle\Entity\Payment
 *
 * @ORM\Table(name="payments")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\PaymentBundle\Entity\PaymentRepository")
 */
class Payment
{
    const STATUS_PENDING = 'pending';
    const STATUS_PAID    = 'paid';

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Кто оплатил. Т.е. провел транзакцию.
     *
     * @var User $user
     *
     * @ORM\ManyToOne(targetEntity="Application\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * Сумма для оплаты
     *
     * @var float $amount
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2)
     */
    private $amount;

    /**
     * Сумма без учета скидки
     *
     * @var float $amountWithoutDiscount
     *
     * @ORM\Column(name="amount_without_discount", type="decimal", precision=10, scale=2)
     */
    private $amountWithoutDiscount;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string")
     */
    private $status;

    /**
     * @var string $gate
     *
     * @ORM\Column()
     */
    private $gate = 'interkassa';

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
     * Указываем или платеж учитывал скидку
     *
     * @var bool
     *
     * @ORM\Column(name="has_discount", type="boolean")
     */
    private $hasDiscount = false;

    /**
     * Constructor. Set default status to new payment.
     *
     * @return void
     */
    public function __construct(User $user, $amount, $hasDiscount = false) {
        $this->setUser($user);
        $this->setAmount($amount);
        $this->setHasDiscount($hasDiscount);
        $this->setStatus(self::STATUS_PENDING);
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
     * Set amount
     *
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
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

    public function isPaid()
    {
        return ($this->getStatus() == self::STATUS_PAID);
    }

    public function getGate()
    {
        return $this->gate;
    }

    public function setGate($gate)
    {
        $this->gate = $gate;
    }

    /**
     * Set hasDiscount
     *
     * @param boolean $hasDiscount
     */
    public function setHasDiscount($hasDiscount)
    {
        $this->hasDiscount = $hasDiscount;
    }

    /**
     * Get hasDiscount
     *
     * @return boolean
     */
    public function getHasDiscount()
    {
        return $this->hasDiscount;
    }

    /**
     * Set amountWithoutDiscount
     *
     * @param float $amountWithoutDiscount
     */
    public function setAmountWithoutDiscount($amountWithoutDiscount)
    {
        $this->amountWithoutDiscount = $amountWithoutDiscount;
    }

    /**
     * Get amountWithoutDiscount
     *
     * @return float
     */
    public function getAmountWithoutDiscount()
    {
        return $this->amountWithoutDiscount;
    }

    /**
     * Get status of payment
     *
     * @return string
     */
    public function __toString()
    {
        return $this->status;
    }
}
