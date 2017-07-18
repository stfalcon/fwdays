<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Stfalcon\Bundle\EventBundle\Traits\Translate;

/**
 * Stfalcon\Bundle\PageBundle\Entity\GeneralPage
 *
 * @ORM\Table(name="pages")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\GeneralPageRepository")
 * @Gedmo\TranslationEntity(class="Stfalcon\Bundle\EventBundle\Entity\Translation\GeneralPageTranslation")
 */
class GeneralPage extends TransBasePage implements Translatable
{
    use Translate;
    /**
     * @ORM\OneToMany(
     *   targetEntity="Stfalcon\Bundle\EventBundle\Entity\Translation\GeneralPageTranslation",
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