<?php

namespace Application\Bundle\UserBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User Class
 *
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="Application\Bundle\UserBundle\Repository\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $fullname
     *
     * @ORM\Column(name="fullname", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    protected $fullname;

    /**
     * @var string $company
     *
     * @ORM\Column(name="company", type="string", length=255, nullable=true)
     */
    protected $company;

    /**
     * @var string $post
     *
     * @ORM\Column(name="post", type="string", length=255, nullable=true)
     */
    protected $post;

    /**
     * @var string $country
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     */
    protected $country;

    /**
     * @var string $city
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    protected $city;

    /**
     * @var boolean $subscribe
     *
     * @ORM\Column(name="subscribe", type="boolean")
     */
    protected $subscribe = true;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="Stfalcon\Bundle\EventBundle\Entity\Ticket", mappedBy="user")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $tickets;


    public function __construct() {
        parent::__construct();
        $this->tickets = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Redefinition email setter for use email as username
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        parent::setEmail($email);
        $this->setUsername($email);
    }

    /**
     * Get user fullname
     *
     * @return string
     */
    public function getFullname()
    {
        return $this->fullname;
    }

    /**
     * Set user fullname
     *
     * @param string $fullname
     */
    public function setFullname($fullname)
    {
        $this->fullname = strip_tags($fullname);
    }

    /**
     * Get user company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set user company
     *
     * @param string $company
     */
    public function setCompany($company)
    {
        $this->company = strip_tags($company);
    }

    /**
     * Get user post
     *
     * @return string
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set user post
     *
     * @param string $post
     */
    public function setPost($post)
    {
        $this->post = strip_tags($post);
    }

    /**
     * User has subscribed to the newsletter?
     *
     * @return bool
     */
    public function isSubscribe()
    {
        return (boolean)$this->subscribe;
    }

    /**
     * Set subscribe
     *
     * @param boolean $subscribe
     */
    public function setSubscribe($subscribe)
    {
        $this->subscribe = $subscribe;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Set city
     *
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = strip_tags($city);
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set country
     *
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = strip_tags($country);
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }


    /**
     * @return mixed
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * @param mixed $tickets
     * @return $this
     */
    public function setTickets($tickets)
    {
        if (count($tickets) > 0) {
            foreach ($tickets as $item) {
                $this->addTicket($item);
            }
        }

        return $this;
    }

    public function addTicket(Ticket $ticket)
    {
        $ticket->setUser($this);
        $this->tickets->add($ticket);
    }

    public function removeTicket(Ticket $ticket)
    {
        $this->tickets->removeElement($ticket);
    }

}
