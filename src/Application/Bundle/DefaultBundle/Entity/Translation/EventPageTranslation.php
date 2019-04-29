<?php

namespace Application\Bundle\DefaultBundle\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use Application\Bundle\DefaultBundle\Entity\AbstractClass\AbstractTranslation;

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
     * @ORM\ManyToOne(targetEntity="Application\Bundle\DefaultBundle\Entity\EventPage", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
