<?php

namespace Stfalcon\Bundle\EventBundle\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;

/**
 * NewsTranslation entity.
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="news_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="news_lookup_unique_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class NewsTranslation extends AbstractTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="Stfalcon\Bundle\EventBundle\Entity\News", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}