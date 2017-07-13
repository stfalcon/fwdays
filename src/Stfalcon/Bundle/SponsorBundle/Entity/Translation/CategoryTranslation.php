<?php

namespace Stfalcon\Bundle\SponsorBundle\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use Stfalcon\Bundle\EventBundle\Entity\Translation\AbstractTranslation;

/**
 * EventTranslation entity.
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="category_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="category_lookup_unique_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class CategoryTranslation extends AbstractTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="Stfalcon\Bundle\SponsorBundle\Entity\Category", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}