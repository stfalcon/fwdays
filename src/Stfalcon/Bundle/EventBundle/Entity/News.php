<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Stfalcon\Bundle\EventBundle\Entity\AbstractClass\AbstractNews;
use Stfalcon\Bundle\EventBundle\Traits\TranslateTrait;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Event.
 *
 * @ORM\Table(name="news")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\NewsRepository")
 *
 * @Gedmo\TranslationEntity(class="Stfalcon\Bundle\EventBundle\Entity\Translation\NewsTranslation")
 */
class News extends AbstractNews implements Translatable
{
    use TranslateTrait;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Stfalcon\Bundle\EventBundle\Entity\Translation\NewsTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}, fetch="EXTRA_LAZY"
     * )
     */
    private $translations;

    /**
     * News constructor.
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }
}
