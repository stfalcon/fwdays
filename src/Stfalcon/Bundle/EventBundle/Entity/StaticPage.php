<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Stfalcon\Bundle\EventBundle\Traits\Translate;

/**
 * Stfalcon\Bundle\PageBundle\Entity\StaticPage
 *
 * @ORM\Table(name="pages")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\StaticPageRepository")
 * @Gedmo\TranslationEntity(class="Stfalcon\Bundle\EventBundle\Entity\Translation\StaticPageTranslation")
 */
class StaticPage extends BasePage implements Translatable
{
    use Translate;
    /**
     * @ORM\OneToMany(
     *   targetEntity="Stfalcon\Bundle\EventBundle\Entity\Translation\StaticPageTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }
}