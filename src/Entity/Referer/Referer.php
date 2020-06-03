<?php

namespace App\Entity\Referer;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Referer.
 *
 * @ORM\Table(name="referrers")
 * @ORM\Entity()
 */
class Referer
{
    public const COOKIE_KEY = 'referer_key';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="from_url", nullable=false)
     *
     * @Assert\NotNull()
     * @Assert\Url()
     */
    private $fromUrl;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="to_url", nullable=false)
     *
     * @Assert\NotNull()
     * @Assert\Url()
     */
    private $toUrl;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @Assert\NotNull()
     */
    private $date;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", name="cookie_id", nullable=true)
     */
    private $cookieId;

    /**
     * @param string $fromUrl
     * @param string $toUrl
     */
    public function __construct(string $fromUrl, string $toUrl)
    {
        $this->date = new \DateTime();
        $this->fromUrl = $fromUrl;
        $this->toUrl = $toUrl;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @return string|null
     */
    public function getCookieId(): ?string
    {
        return $this->cookieId;
    }

    /**
     * @param string|null $cookieId
     */
    public function setCookieId(?string $cookieId): void
    {
        $this->cookieId = $cookieId;
    }

    /**
     * @return string
     */
    public function getFromUrl(): string
    {
        return $this->fromUrl;
    }

    /**
     * @return string
     */
    public function getToUrl(): string
    {
        return $this->toUrl;
    }
}
