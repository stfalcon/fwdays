<?php

namespace Application\Bundle\UserBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

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
     * @ORM\Column(name="fullname", type="string", length=255, nullable=true)
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
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var string $city
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    protected $city;

    /**
     * @var string $country
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     */
    protected $country;
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

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get user country
     *
     * @return string
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * Set user country
     *
     * @param string $country
     * @return User
     */
    public function setCountry($country) {
        $this->country = $country;

        return $this;
    }

    /**
     * Get user city
     *
     * @return string
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * Set user city
     *
     * @param string $city
     * @return User
     */
    public function setCity($city) {
        $this->city = $city;

        return $this;
    }

}