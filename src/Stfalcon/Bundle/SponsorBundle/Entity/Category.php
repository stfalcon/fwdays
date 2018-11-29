<?php

namespace Stfalcon\Bundle\SponsorBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Stfalcon\Bundle\EventBundle\Traits\TranslateTrait;

/**
 * Stfalcon\Bundle\SponsorBundle\Entity\Category.
 *
 * @ORM\Table(name="sponsors_category")
 *
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\SponsorBundle\Repository\CategoryRepository")
 *
 * @Gedmo\TranslationEntity(class="Stfalcon\Bundle\SponsorBundle\Entity\Translation\CategoryTranslation")
 */
class Category implements Translatable
{
    use TranslateTrait;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Stfalcon\Bundle\SponsorBundle\Entity\Translation\CategoryTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @Gedmo\Translatable(fallback=true)
     */
    private $name;

    /**
     * is category in wide container.
     *
     * @var bool
     *
     * @ORM\Column(name="is_wide_container", type="boolean", options={"default":"0"})
     */
    private $isWideContainer = false;

    /**
     * @var int
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=false)
     */
    protected $sortOrder = 1;

    /**
     * Category constructor.
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName() ?: '-';
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set sortOrder.
     *
     * @param int $sortOrder
     *
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * Get sortOrder.
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @return bool
     */
    public function isWideContainer()
    {
        return $this->isWideContainer;
    }

    /**
     * @param bool $isWideContainer
     *
     * @return $this
     */
    public function setIsWideContainer($isWideContainer)
    {
        $this->isWideContainer = $isWideContainer;

        return $this;
    }
}
