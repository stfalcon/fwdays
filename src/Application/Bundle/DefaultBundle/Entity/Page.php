<?php

namespace Application\Bundle\DefaultBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Application\Bundle\DefaultBundle\Entity\AbstractClass\AbstractPage;
use Application\Bundle\DefaultBundle\Traits\TranslateTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="pages")
 * @ORM\Entity(repositoryClass="Application\Bundle\DefaultBundle\Repository\PageRepository")
 *
 * @UniqueEntity(
 *     "slug",
 *     errorPath="slug",
 *     message="Slug has to contin unique value."
 * )
 * @Gedmo\TranslationEntity(class="Application\Bundle\DefaultBundle\Entity\Translation\PageTranslation")
 */
class Page extends AbstractPage implements Translatable
{
    use TranslateTrait;
    /**
     * @ORM\OneToMany(
     *   targetEntity="Application\Bundle\DefaultBundle\Entity\Translation\PageTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

    /**
     * @var bool
     *
     * @ORM\Column(name="show_in_footer", type="boolean")
     */
    protected $showInFooter = false;

    /**
     * Page constructor.
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function isShowInFooter()
    {
        return $this->showInFooter;
    }

    /**
     * @param bool $showInFooter
     *
     * @return $this
     */
    public function setShowInFooter($showInFooter)
    {
        $this->showInFooter = $showInFooter;

        return $this;
    }
}
