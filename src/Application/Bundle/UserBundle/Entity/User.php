<?php

namespace Application\Bundle\UserBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
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
     * @ORM\Column(name="fullname", type="string", length=255)
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
     * @var boolean $subscribe
     *
     * @ORM\Column(name="subscribe", type="boolean")
     */
    protected $subscribe = true;

    /**
     * @var string $text
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    protected $comment;

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
    public function getFullname() {
        return $this->fullname;
    }

    /**
     * Set user fullname
     *
     * @param string $fullname
     */
    public function setFullname($fullname) {
        $this->fullname = $fullname;
    }

    /**
     * Get user company
     *
     * @return string
     */
    public function getCompany() {
        return $this->company;
    }

    /**
     * Set user company
     *
     * @param string $company
     */
    public function setCompany($company) {
        $this->company = $company;
    }

    /**
     * Get user post
     *
     * @return string
     */
    public function getPost() {
        return $this->post;
    }

    /**
     * Set user post
     *
     * @param string $post
     */
    public function setPost($post) {
        $this->post = $post;
    }

    /**
     * User has subscribed to the newsletter?
     *
     * @return string
     */
    public function isSubscribe() {
        return $this->subscribe;
    }

    /**
     * Set subscribe
     *
     * @param type $subscribe
     */
    public function setSubscribe($subscribe) {
        $this->subscribe = $subscribe;
    }

    /**
     * Get comment
     *
     * @todo: rm this method
     * @return type
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * Set comment
     *
     * @todo: rm this method
     * @param type $comment
     */
    public function setComment($comment) {
        $this->comment = $comment;
    }

}