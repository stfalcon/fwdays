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
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Event")
     * @ORM\JoinTable(name="event__events_mails",
     *   joinColumns={
     *     @ORM\JoinColumn(name="mail_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     *   }
     * )
     */    
    private $events;
    
    /**
     * @var boolean $start
     * 
     * @ORM\Column(name="start", type="boolean", nullable=true)
     */
    private $start;
    
    /**
     * @var boolean $complete
     * 
     * @ORM\Column(name="complete", type="boolean", nullable=true)
     */
    private $complete;
    
    public function __construct()
    {
        $this->events = new ArrayCollection();
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

    public function getEvents() {
        return $this->events;
    }

    public function setEvents($events) {
        $this->events = $events;
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
        foreach ($data as $key => $value) {
            return preg_replace('/' . $key .'/', $value, $this->getText());
        }
    }

}