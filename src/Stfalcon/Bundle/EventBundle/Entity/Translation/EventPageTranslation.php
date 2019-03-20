<?php

namespace Stfalcon\Bundle\EventBundle\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use Stfalcon\Bundle\EventBundle\Entity\AbstractClass\AbstractTranslation;

/**
 * EventPageTranslation entity.
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="event_page_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="event_page_lookup_unique_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class EventPageTranslation extends AbstractTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="Stfalcon\Bundle\EventBundle\Entity\EventPage", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
