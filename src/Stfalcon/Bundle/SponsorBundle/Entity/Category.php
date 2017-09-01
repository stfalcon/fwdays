<?php

namespace Stfalcon\Bundle\SponsorBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Stfalcon\Bundle\EventBundle\Traits\Translate;

/**
 * Stfalcon\Bundle\SponsorBundle\Entity\Category
 *
 * @ORM\Table(name="sponsors_category")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\SponsorBundle\Repository\CategoryRepository")
 * @Gedmo\TranslationEntity(class="Stfalcon\Bundle\SponsorBundle\Entity\Translation\CategoryTranslation")
 */
class Category implements Translatable
{
    use Translate;
    /**
     * @var integer $id
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Gedmo\Translatable(fallback=true)
     */
    private $name;

    /**
     * @var int $sortOrder
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=false)
     */
    protected $sortOrder = 1;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * Get title
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName() ?: '-';
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

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set sortOrder
     *
     * @param int $sortOrder
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * Get sortOrder
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }
}
