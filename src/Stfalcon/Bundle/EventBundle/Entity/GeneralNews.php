<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Stfalcon\Bundle\EventBundle\Traits\Translate;
use Stfalcon\Bundle\NewsBundle\Entity\BaseNews;
use Symfony\Component\Validator\Constraints as Assert;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Stfalcon\Bundle\EventBundle\Entity\Event
 *
 * @ORM\Table(name="news")
 * @ORM\Entity(repositoryClass="Stfalcon\Bundle\EventBundle\Repository\GeneralNewsRepository")
 * @Gedmo\TranslationEntity(class="Stfalcon\Bundle\EventBundle\Entity\Translation\GeneralNewsTranslation")
 */
class GeneralNews extends TransBaseNews implements Translatable
{
    use Translate;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Stfalcon\Bundle\EventBundle\Entity\Translation\GeneralNewsTranslation",
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
