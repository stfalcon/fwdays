<?php

namespace App\Entity;

use App\Service\LocalsRequiredService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use FOS\UserBundle\Model\UserInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User.
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="users")
 *
 * @UniqueEntity(fields={"emailCanonical"}, errorPath="email", groups={"registration", "profile"})
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="fullname", type="string", length=255, nullable=true)
     */
    protected $fullname;

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=255, nullable=true)
     *
     * @Assert\Length(
     *     min = 2,
     *     max = 72,
     *     groups={"profile"}
     * )
     */
    protected $company;

    /**
     * @var string
     *
     * @ORM\Column(name="post", type="string", length=255, nullable=true)
     *
     * @Assert\Length(
     *     min = 2,
     *     max = 72,
     *     groups={"profile"}
     * )
     */
    protected $post;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     *
     * @Assert\Length(
     *     min = 2,
     *     max = 72,
     *     groups={"profile"}
     * )
     * @Assert\Regex(
     *     pattern="/^[\pL\-\s']+$/u",
     *     match=true,
     *     groups={"profile"}
     * )
     */
    protected $country;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     *
     * @Assert\Length(
     *     min = 2,
     *     max = 72,
     *     groups={"profile"}
     * )
     * @Assert\Regex(
     *     pattern="/^[\pL\-\s']+$/u",
     *     match=true,
     *     groups={"profile"}
     * )
     */
    protected $city;

    /**
     * @var bool
     *
     * @ORM\Column(name="subscribe", type="boolean")
     */
    protected $subscribe = true;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="unsubscribed_at", type="datetime", nullable=true)
     */
    private $unsubscribedAt = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var bool Allow share contacts
     *
     * @ORM\Column(name="allow_share_contacts", type="boolean", options={"default" : null}, nullable=true)
     */
    private $allowShareContacts;

    /**
     * @var Ticket[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Ticket", mappedBy="user")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $tickets;

    /**
     * Подіі в яких юзер бажає прийняти участь.
     *
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Event")
     * @ORM\JoinTable(name="user_wants_visit_event",
     *   joinColumns={
     *     @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     *   }
     * )
     * @ORM\OrderBy({"date" = "DESC"})
     */
    protected $wantsToVisitEvents;

    /**
     * @var UserEventRegistration[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\UserEventRegistration", mappedBy="user", orphanRemoval=true)
     */
    private $eventRegistrations;

    /**
     * @var string
     *
     * @ORM\Column(name="referral_code", type="string", length=50, nullable=true)
     */
    protected $referralCode;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_ref_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $userReferral;

    /**
     * @var float|null
     *
     * @ORM\Column(name="balance", type="decimal", precision=10, scale=2, nullable=true, options = {"default" : 0})
     *
     * @Groups("payment.view")
     */
    protected $balance = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     *
     * @Assert\NotBlank(groups={"registration", "profile"})
     * @Assert\Regex(
     *     pattern="/^[\pL\-\s']+$/u",
     *     match=true,
     *     message="error.name.only_letters",
     *     groups={"registration", "profile"}
     * )
     * @Assert\Length(
     *     min = 2,
     *     max = 32,
     *     groups={"registration", "profile"}
     * )
     *
     * @Groups("payment.view")
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="surname", type="string", length=255, nullable=false)
     *
     * @Assert\NotBlank(groups={"registration", "profile"})
     * @Assert\Regex(
     *     pattern="/^[\pL\-\s']+$/u",
     *     match=true,
     *     message="error.surname.only_letters",
     *     groups={"registration", "profile"}
     * )
     * @Assert\Length(
     *     min = 2,
     *     max = 32,
     *     groups={"registration", "profile"}
     * )
     *
     * @Groups("payment.view")
     */
    protected $surname;

    /**
     * @var string|null
     *
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     *
     * @Assert\Regex(
     *     pattern="/\+[1-9]{1}[0-9]{10,14}$/i",
     *     match=true,
     *     message="error.phone_bad_format",
     *     groups={"registration", "profile"}
     * )
     */
    protected $phone;

    /**
     * @var string
     *
     * @Assert\Email(message="error.email_bad_format", strict="true", groups={"registration", "profile"})
     * @Assert\NotBlank(groups={"registration", "profile"})
     *
     * @Groups("payment.view")
     */
    protected $email;

    /**
     * @var bool
     *
     * @ORM\Column(name="email_exists", type="boolean", nullable=true, options = {"default" : 1})
     */
    protected $emailExists = true;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook_id", type="string", nullable=true)
     */
    private $facebookID;

    /**
     * @var string
     *
     * @ORM\Column(name="google_id", type="string", nullable=true)
     */
    private $googleID;

    /**
     * @var string
     *
     * @Assert\Length(
     *     min = 2,
     *     max = 72,
     *     groups={"registration"}
     * )
     */
    protected $plainPassword;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $recToken;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="email_language", nullable=false, options={"default":"uk"})
     *
     * @Assert\NotBlank(groups={"registration", "profile"})
     * @Assert\Length(min="2", groups={"registration", "profile"})
     */
    private $emailLanguage = LocalsRequiredService::UK_EMAIL_LANGUAGE;

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->tickets = new ArrayCollection();
        $this->wantsToVisitEvents = new ArrayCollection();
        $this->eventRegistrations = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function isEmailExists()
    {
        return $this->emailExists;
    }

    /**
     * @param bool $emailExists
     *
     * @return $this
     */
    public function setEmailExists($emailExists)
    {
        $this->emailExists = $emailExists;

        return $this;
    }

    /**
     * @return string
     */
    public function getFacebookID()
    {
        return $this->facebookID;
    }

    /**
     * @param string $facebookID
     *
     * @return $this
     */
    public function setFacebookID($facebookID)
    {
        $this->facebookID = $facebookID;

        return $this;
    }

    /**
     * @return string
     */
    public function getGoogleID()
    {
        return $this->googleID;
    }

    /**
     * @param string $googleID
     *
     * @return $this
     */
    public function setGoogleID($googleID)
    {
        $this->googleID = $googleID;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getWantsToVisitEvents()
    {
        return $this->wantsToVisitEvents;
    }

    /**
     * @param Event $event
     *
     * @return bool
     */
    public function isRegisterToEvent(Event $event): bool
    {
        return $this->eventRegistrations->exists(
            function (int $key, UserEventRegistration $collectionElement) use ($event) {
                return $collectionElement->getEvent()->isEqualTo($event);
            }
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        if (empty($this->name) && !empty($this->fullname)) {
            $name = \explode(' ', $this->fullname, 2);
            $firstName = isset($name[0]) ? trim($name[0]) : '';
            $this->name = $firstName;
        }

        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = \strip_tags(\trim($name));
        $this->setFullname($this->name.' '.$this->surname);

        return $this;
    }

    /**
     * @return string
     */
    public function getSurname()
    {
        if (empty($this->surname) && !empty($this->fullname)) {
            $name = \explode(' ', $this->fullname, 2);
            $lastName = isset($name[1]) ? trim($name[1]) : '';
            $this->surname = $lastName;
        }

        return $this->surname;
    }

    /**
     * @param string $surname
     *
     * @return $this
     */
    public function setSurname($surname)
    {
        $this->surname = \strip_tags(\trim($surname));
        $this->setFullname($this->name.' '.$this->surname);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     *
     * @return $this
     */
    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Redefinition email setter for use email as username.
     *
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        parent::setEmail($email);
        $this->setUsername($email)
            ->setEmailExists(true);

        return $this;
    }

    /**
     * Get user fullname.
     *
     * @return string
     */
    public function getFullname()
    {
        if (empty($this->fullname)) {
            $this->setFullname($this->name.' '.$this->surname);
        }

        return $this->fullname;
    }

    /**
     * Set user fullname.
     *
     * @param string $fullname
     *
     * @return $this
     */
    public function setFullname($fullname)
    {
        $this->fullname = strip_tags($fullname);

        return $this;
    }

    /**
     * Get user company.
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set user company.
     *
     * @param string $company
     *
     * @return $this
     */
    public function setCompany($company)
    {
        $this->company = strip_tags($company);

        return $this;
    }

    /**
     * Get user post.
     *
     * @return string
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set user post.
     *
     * @param string $post
     *
     * @return $this
     */
    public function setPost($post)
    {
        $this->post = strip_tags($post);

        return $this;
    }

    /**
     * User has subscribed to the newsletter?
     *
     * @return bool
     */
    public function isSubscribe(): bool
    {
        return $this->subscribe;
    }

    /**
     * Set subscribe.
     *
     * @param bool $subscribe
     *
     * @return $this
     */
    public function setSubscribe(bool $subscribe): self
    {
        $this->subscribe = $subscribe;

        if (!$subscribe) {
            $this->unsubscribedAt = new \DateTime();
        }

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Set city.
     *
     * @param string $city
     *
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = strip_tags($city);

        return $this;
    }

    /**
     * Get city.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set country.
     *
     * @param string $country
     *
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = strip_tags($country);

        return $this;
    }

    /**
     * Get country.
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
     *
     * @return $this
     */
    public function setTickets($tickets)
    {
        if (\count($tickets) > 0) {
            foreach ($tickets as $item) {
                $this->addTicket($item);
            }
        }

        return $this;
    }

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function addTicket(Ticket $ticket)
    {
        if (!$this->tickets->contains($ticket)) {
            $ticket->setUser($this);
            $this->tickets->add($ticket);
        }

        return $this;
    }

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function removeTicket(Ticket $ticket)
    {
        if ($this->tickets->contains($ticket)) {
            $this->tickets->removeElement($ticket);
        }

        return $this;
    }

    /**
     * @return float|null
     */
    public function getBalance(): ?float
    {
        return $this->balance;
    }

    /**
     * @param float $balance
     *
     * @return $this
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReferralCode()
    {
        return $this->referralCode;
    }

    /**
     * @param mixed $referralCode
     *
     * @return $this
     */
    public function setReferralCode($referralCode)
    {
        $this->referralCode = $referralCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserReferral()
    {
        return $this->userReferral;
    }

    /**
     * @param mixed $userReferral
     *
     * @return $this
     */
    public function setUserReferral($userReferral)
    {
        $this->userReferral = $userReferral;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowShareContacts()
    {
        return $this->allowShareContacts;
    }

    /**
     * @param bool $allowShareContacts
     *
     * @return $this
     */
    public function setAllowShareContacts($allowShareContacts)
    {
        $this->allowShareContacts = $allowShareContacts;

        return $this;
    }

    /**
     * @return string
     */
    public function getRecToken()
    {
        return $this->recToken;
    }

    /**
     * @param string $recToken
     *
     * @return $this
     */
    public function setRecToken($recToken)
    {
        $this->recToken = $recToken;

        return $this;
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        if ($user->getId() !== $this->getId()) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getEmailLanguage(): string
    {
        return $this->emailLanguage;
    }

    /**
     * @param string $emailLanguage
     *
     * @return $this
     */
    public function setEmailLanguage(string $emailLanguage): self
    {
        $this->emailLanguage = $emailLanguage;

        return $this;
    }

    /**
     * @return UserEventRegistration[]|Collection
     */
    public function getEventRegistrations()
    {
        return $this->eventRegistrations;
    }

    /**
     * @param UserEventRegistration[]|Collection $eventRegistrations
     *
     * @return $this
     */
    public function setEventRegistrations($eventRegistrations): self
    {
        $this->eventRegistrations = $eventRegistrations;

        return $this;
    }

    /**
     * @param UserEventRegistration $registration
     *
     * @return bool
     */
    public function addUserEventRegistration(UserEventRegistration $registration): bool
    {
        $registrationExists = $this->eventRegistrations->exists(
            function (int $key, UserEventRegistration $collectionElement) use ($registration) {
                return $collectionElement->getEvent()->isEqualTo($registration->getEvent());
            }
        );

        if (!$registrationExists) {
            $this->eventRegistrations->add($registration);

            return $registration->getEvent()->addWantsToVisitCount();
        }

        return false;
    }

    /**
     * @param Event $event
     *
     * @return bool
     */
    public function removeUserEventRegistration(Event $event): bool
    {
        foreach ($this->eventRegistrations as $registration) {
            if ($registration->getEvent()->isEqualTo($event)) {
                $this->eventRegistrations->removeElement($registration);

                return $event->subtractWantsToVisitCount();
            }
        }

        return false;
    }
}
