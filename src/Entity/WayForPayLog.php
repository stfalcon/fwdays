<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class WayForPayLog.
 *
 * @ORM\Table(name="wayforpay_logs")
 * @ORM\Entity()
 */
class WayForPayLog
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @var Payment
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Payment")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $payment;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $responseData;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $fwdaysResponse;

    /**
     * WayForPayLog constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param Payment $payment
     *
     * @return $this
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getResponseData()
    {
        return $this->responseData;
    }

    /**
     * @param string $responseData
     *
     * @return $this
     */
    public function setResponseData($responseData)
    {
        $this->responseData = $responseData;

        return $this;
    }

    /**
     * @return string
     */
    public function getFwdaysResponse()
    {
        return $this->fwdaysResponse;
    }

    /**
     * @param string $fwdaysResponse
     *
     * @return $this
     */
    public function setFwdaysResponse($fwdaysResponse)
    {
        $this->fwdaysResponse = $fwdaysResponse;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResponseAsArray()
    {
        return unserialize($this->responseData);
    }
}
