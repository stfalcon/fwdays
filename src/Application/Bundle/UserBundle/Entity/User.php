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
    protected $subscribe;

//    public function __construct()
//    {
//        parent::__construct();
//        // your own logic
//    }
    
    public function setEmail($email) 
    { 
             parent::setEmail($email); 
             $this->setUsername($email); 
    } 

    public function getFullname() {
        return $this->fullname;
    }

    public function setFullname($fullname) {
        $this->fullname = $fullname;
    }

    public function getCompany() {
        return $this->company;
    }

    public function setCompany($company) {
        $this->company = $company;
    }

    public function getPost() {
        return $this->post;
    }

    public function setPost($post) {
        $this->post = $post;
    }
    
    public function isSubscribe() {
        return $this->subscribe;
    }

    public function setSubscribe($subscribe) {
        $this->subscribe = $subscribe;
    }



}