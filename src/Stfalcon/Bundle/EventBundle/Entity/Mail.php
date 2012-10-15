<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Mail
 *
 * @ORM\Table(name="event__mails")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\MailRepository")
 */
class Mail
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
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var text $text
     *
     * @ORM\Column(name="text", type="text")
     */
    private $text;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $event;

    /**
     * @var boolean $start
     *
     * @ORM\Column(name="start", type="boolean")
     */
    private $start = false;

    /**
     * @var boolean $complete
     *
     * @ORM\Column(name="complete", type="boolean")
     */
    private $complete = false;

    /**
     * @todo refact. это костыльное и временное решение
     * @var string $text
     *
     * @ORM\Column(name="payment_status", type="string", nullable=true)
     */
    private $paymentStatus = null;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getText() {
        return $this->text;
    }

    public function setText($text) {
        $this->text = $text;
    }

    /**
     * @return Event
     */
    public function getEvent() {
        return $this->event;
    }

    /**
     * @param Event|null $event
     * @return void
     */
    public function setEvent($event) {
        $this->event = $event;
    }

    public function getStart() {
        return $this->start;
    }

    public function setStart($start) {
        $this->start = $start;
    }

    public function getComplete() {
        return $this->complete;
    }

    public function setComplete($complete) {
        $this->complete = $complete;
    }

    public function replace($data) {
        $text = $this->getText();
        foreach ($data as $key => $value) {
            $text = str_replace($key, $value, $text);
        }
        return $text;
    }

    public function getPaymentStatus() {
        return $this->paymentStatus;
    }

    public function setPaymentStatus($paymentStatus) {
        $this->paymentStatus = $paymentStatus;
    }

}
