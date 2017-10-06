<?php
/**
 * Created by PhpStorm.
 * User: webdev
 * Date: 5/4/17
 * Time: 10:23 AM
 */

namespace Application\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UserBalance Class
 *
 * @ORM\Entity
 * @ORM\Table(name="users_balance")
 * @ORM\Entity(repositoryClass="Application\Bundle\UserBundle\Repository\UserBalanceRepository")
 */
class UserBalance
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Юзер по якому проводиться баланс
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Application\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * Оплата, яка впливає на баланс
     *
     * @var \Stfalcon\Bundle\EventBundle\Entity\Payment
     *
     * @ORM\ManyToOne(targetEntity="Stfalcon\Bundle\EventBundle\Entity\Payment")
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
     * Сума операціі (+ або -)
     *
     * @var float $operationAmount
     *
     * @ORM\Column(name="operation_amount", type="decimal", precision=10, scale=2)
     */
    private $operationAmount = 0;

    /**
     * Загальний/кінцевий баланс користувача після цієї операціі
     *
     * @var float $balance
     *
     * @ORM\Column(name="balance", type="decimal", precision=10, scale=2)
     */
    private $balance;

    /**
     * Опис операціі
     *
     * @var string $description
     *
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $user
     * @return $this
     */

    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**+
     * @param $payment
     * @return $this
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * @return \Stfalcon\Bundle\EventBundle\Entity\Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param $operationAmount
     * @return $this
     */
    public function setOperationAmount($operationAmount)
    {
        $this->operationAmount = $operationAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getOperationAmount()
    {
        return $this->operationAmount;
    }

    /**
     * @param $balance
     * @return $this
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * @return float
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
