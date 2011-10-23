<?php

namespace Stfalcon\Bundle\PaymentsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Stfalcon\Bundle\PaymentsBundle\Entity\Payment
 *
 * @ORM\Table(name="payments")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\PaymentsBundle\Entity\PaymentRepository")
 */
class Payment
{

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int $userId
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var decimal $sum
     *
     * @ORM\Column(name="sum", type="decimal")
     */
    private $sum;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string")
     */
    private $status;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set sum
     *
     * @param decimal $sum
     */
    public function setSum($sum)
    {
        $this->sum = $sum;
    }

    /**
     * Get sum
     *
     * @return decimal
     */
    public function getSum()
    {
        return $this->sum;
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
     * @param integer $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
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

}