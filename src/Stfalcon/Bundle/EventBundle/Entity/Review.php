<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Application\Bundle\UserBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Stfalcon\Bundle\EventBundle\Traits\Translate;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Review
 *
 * @ORM\Table(name="event__reviews")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\ReviewRepository")
 * @Gedmo\TranslationEntity(class="Stfalcon\Bundle\EventBundle\Entity\Translation\ReviewTranslation")
 */
class Review extends BasePage implements Translatable
{
    use Translate;
    /**
     * @ORM\OneToMany(
     *   targetEntity="Stfalcon\Bundle\EventBundle\Entity\Translation\ReviewTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Speaker")
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
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Application\Bundle\UserBundle\Entity\User")
     * @ORM\JoinTable(name="reviews_users_likes",
     *      joinColumns={@ORM\JoinColumn(name="review_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     */
    private $likedUsers;

    public function __construct()
    {
        $this->speakers = new ArrayCollection();
        $this->likedUsers = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function getEvent() {
        return $this->event;
    }

    public function setEvent($event) {
        $this->event = $event;
    }
    
    public function getSpeakers() {
        return $this->speakers;
    }

    public function setSpeaker($speakers) {
        $this->speakers = $speakers;
    }

    /**
     * @param ArrayCollection $likedUsers
     */
    public function setLikedUsers($likedUsers)
    {
        $this->likedUsers = $likedUsers;
    }

    /**
     * @return ArrayCollection
     */
    public function getLikedUsers()
    {
        return $this->likedUsers;
    }

    /**
     * @param User $user
     */
    public function addLikedUser($user)
    {
        if (!$this->likedUsers->contains($user)) {
            $this->likedUsers->add($user);
        }
    }

    /**
     * @param User $user
     */
    public function removeLikedUser($user)
    {
        $this->likedUsers->removeElement($user);
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isLikedByUser($user)
    {
        return $this->likedUsers->contains($user);
    }
}