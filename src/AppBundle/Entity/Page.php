<?php

namespace App\Entity;

use App\Entity\AbstractClass\AbstractPage;
use App\Traits\TranslateTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="pages")
 * @ORM\Entity(repositoryClass="App\Repository\PageRepository")
 *
 * @UniqueEntity(
 *     "slug",
 *     errorPath="slug",
 *     message="Поле slug повинне бути унікальне."
 * )
 * @Gedmo\TranslationEntity(class="App\Entity\Translation\PageTranslation")
 */
class Page extends AbstractPage implements Translatable
{
    use TranslateTrait;
    /**
     * @ORM\OneToMany(
     *   targetEntity="App\Entity\Translation\PageTranslation",
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
    public function isShowInFooter(): bool
    {
        return $this->showInFooter;
    }

    /**
     * @param bool $showInFooter
     *
     * @return $this
     */
    public function setShowInFooter($showInFooter): self
    {
        $this->showInFooter = $showInFooter;

        return $this;
    }
}
