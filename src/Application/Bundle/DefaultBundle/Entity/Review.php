<?php

namespace Application\Bundle\DefaultBundle\Entity;

use Application\Bundle\DefaultBundle\Entity\AbstractClass\AbstractPage;
use Application\Bundle\DefaultBundle\Traits\TranslateTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Application\Bundle\DefaultBundle\Entity\Review.
 *
 * @ORM\Table(name="event__reviews")
 * @ORM\Entity(repositoryClass="Application\Bundle\DefaultBundle\Repository\ReviewRepository")
 *
 * @UniqueEntity(
 *     "slug",
 *     errorPath="slug",
 *     message="Поле slug повинне бути унікальне."
 * )
 *
 * @Gedmo\TranslationEntity(class="Application\Bundle\DefaultBundle\Entity\Translation\ReviewTranslation")
 */
class Review extends AbstractPage implements Translatable
{
    use TranslateTrait;
    /**
     * @ORM\OneToMany(
     *   targetEntity="Application\Bundle\DefaultBundle\Entity\Translation\ReviewTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;

    /**
     * @var Speaker[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="Speaker", inversedBy="reviews")
     * @ORM\JoinTable(name="event__speakers_reviews",
     *   joinColumns={
     *     @ORM\JoinColumn(name="review_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="speaker_id", referencedColumnName="id")
     *   }
     * )
     */
    private $speakers;

    /**
     * @var User[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="Application\Bundle\DefaultBundle\Entity\User")
     * @ORM\JoinTable(name="reviews_users_likes",
     *      joinColumns={@ORM\JoinColumn(name="review_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private $likedUsers;

    /**
     * @var string
     *
     * @ORM\Column(name="keywords", type="string", nullable=true)
     */
    protected $keywords;

    /**
     * Review constructor.
     */
    public function __construct()
    {
        $this->speakers = new ArrayCollection();
        $this->likedUsers = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     *
     * @return $this
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getSpeakers()
    {
        return $this->speakers;
    }

    /**
     * @param Speaker[]|Collection $speakers
     *
     * @return $this
     */
    public function setSpeaker($speakers): self
    {
        $this->speakers = $speakers;

        return $this;
    }

    /**
     * @param User[]|Collection $likedUsers
     *
     * @return $this
     */
    public function setLikedUsers($likedUsers): self
    {
        $this->likedUsers = $likedUsers;

        return $this;
    }

    /**
     * @return User[]|Collection
     */
    public function getLikedUsers()
    {
        return $this->likedUsers;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function addLikedUser(User $user): self
    {
        if (!$this->likedUsers->contains($user)) {
            $this->likedUsers->add($user);
        }

        return $this;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function removeLikedUser(User $user): self
    {
        if ($this->likedUsers->contains($user)) {
            $this->likedUsers->removeElement($user);
        }

        return $this;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isLikedByUser($user): bool
    {
        return $this->likedUsers->contains($user);
    }

    /**
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param string $keywords
     *
     * @return $this
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }
}
