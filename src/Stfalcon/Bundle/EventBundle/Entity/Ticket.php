<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Stfalcon\Bundle\PaymentBundle\Entity\Payment;
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
     * @var Stfalcon\Bundle\PaymentBundle\Entity\Payment
     *
     * @ORM\ManyToOne(targetEntity="Stfalcon\Bundle\PaymentBundle\Entity\Payment")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id", onDelete="CASCADE")
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

    public function __construct(Event $event, User $user) {
        $this->setEvent($event);
        $this->setUser($user);
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
     * @return Event
     */
    public function getEvent() {
        return $this->event;
    }

    /**
     * @param Event $event
     * @return void
     */
    private function setEvent(Event $event) {
        $this->event = $event;
    }

    /**
     * @return Payment
     */
    public function getPayment() {
        return $this->payment;
    }

    /**
     * @param Payment $payment
     * @return void
     */
    public function setPayment(Payment $payment) {
        $this->payment = $payment;
    }

    /**
     * @param User $user
     */
    private function setUser(User $user)
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
        return (bool) ($this->getPayment() != null && $this->getPayment()->isPaid());
    }

    /**
     * Mark ticket as "used"
     *
     * @param bool $used
     */
    public function setUsed($used){
        $this->used = $used;
    }

    /**
     * Checking if ticket is "used"
     *
     * @return bool
     */
    public function isUsed(){
        return $this->used;
    }

    /**
     * Generate unique md5 hash for ticket
     * @return string
     */
    public function getHash()
    {
        return md5($this->getId() . $this->getCreatedAt()->format('Y-m-d H:i:s'));
    }

}
